<?php
// app/Services/UserService.php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService
{
    const DEFAULT_PASSWORD = 'CAMBIAME';

    public function getAllUsers(array $filters = [])
    {
        $query = User::query();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'ILIKE', "%{$filters['search']}%")
                    ->orWhere('email', 'ILIKE', "%{$filters['search']}%");
            });
        }

        if (isset($filters['active'])) {
            $active = $filters['active'] === 'true' || $filters['active'] === '1';
            $query->where('active', $active);
        }

        $pagination = $filters['paginate'] ?? 15;

        return $query->with(['roles' => function ($query) {
            $query->where('roles.active', true)
                ->where('role_user.active', true);
        }, 'employee.position'])
            ->orderBy('name')
            ->simplePaginate($pagination);
    }

    public function getUserById($id)
    {
        return User::with(['roles' => function ($query) {
            $query->where('roles.active', true)
                ->where('role_user.active', true);
        }, 'employee.position'])
            ->findOrFail($id);
    }

    public function createUser(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Verificar si el employee_id ya tiene un usuario
            if (!empty($data['employee_id'])) {
                $existingUser = User::where('employee_id', $data['employee_id'])->first();
                if ($existingUser) {
                    throw new \Exception('Este personal ya tiene un usuario asignado.');
                }
            }

            // Crear el usuario
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'employee_id' => $data['employee_id'] ?? null,
                'active' => $data['active'] ?? true,
            ]);

            // Asignar roles
            if (!empty($data['roles'])) {
                $rolesData = [];
                foreach ($data['roles'] as $roleId) {
                    $rolesData[$roleId] = ['active' => true];
                }
                $user->roles()->attach($rolesData);
            }

            return $user->load('roles', 'employee.position');
        });
    }

    public function updateUser($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $user = User::findOrFail($id);

            // Verificar si el employee_id ya está asignado a otro usuario
            if (isset($data['employee_id']) && $data['employee_id']) {
                $existingUser = User::where('employee_id', $data['employee_id'])
                    ->where('id', '!=', $id)
                    ->first();
                    
                if ($existingUser) {
                    throw new \Exception('Este personal ya tiene un usuario asignado.');
                }
            }

            // Actualizar datos básicos
            $updateData = [
                'name' => $data['name'] ?? $user->name,
                'email' => $data['email'] ?? $user->email,
            ];

            // Actualizar employee_id solo si está presente en los datos
            if (isset($data['employee_id'])) {
                $updateData['employee_id'] = $data['employee_id'];
            }

            if (isset($data['active'])) {
                $updateData['active'] = $data['active'];
            }

            $user->update($updateData);

            // Actualizar roles si están presentes
            if (isset($data['roles'])) {
                $user->roles()->detach();
                
                if (!empty($data['roles'])) {
                    $rolesData = [];
                    foreach ($data['roles'] as $roleId) {
                        $rolesData[$roleId] = ['active' => true];
                    }
                    $user->roles()->attach($rolesData);
                }
            }

            return $user->load('roles', 'employee.position');
        });
    }

    public function deleteUser($id)
    {
        return DB::transaction(function () use ($id) {
            $user = User::findOrFail($id);
            
            // Verificar que no sea el último admin
            if ($user->hasRole('admin')) {
                $adminCount = User::whereHas('roles', function ($query) {
                    $query->where('name', 'admin')
                        ->where('roles.active', true)
                        ->where('role_user.active', true);
                })->where('active', true)->count();

                if ($adminCount <= 1) {
                    throw new \Exception('No se puede eliminar el último administrador del sistema.');
                }
            }

            // Desvincular roles
            $user->roles()->detach();
            
            // Eliminar usuario
            $user->delete();
            
            return true;
        });
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'password' => Hash::make(self::DEFAULT_PASSWORD)
        ]);
        
        return $user;
    }

    public function changePassword($id, array $data)
    {
        $user = User::findOrFail($id);

        if (!Hash::check($data['current_password'], $user->password)) {
            throw new \Exception('La contraseña actual es incorrecta');
        }

        $user->update([
            'password' => Hash::make($data['new_password'])
        ]);

        return $user;
    }

    public function needsPasswordReset($id)
    {
        $user = User::findOrFail($id);
        return Hash::check(self::DEFAULT_PASSWORD, $user->password);
    }

    public function getAvailableEmployees()
    {
        // Debug: Obtener TODOS los empleados activos
        $allEmployees = \App\Models\Employee::where('active', true)
            ->with('position')
            ->orderBy('firstname')
            ->get();
        
        \Illuminate\Support\Facades\Log::info('Total empleados activos: ' . $allEmployees->count());
        
        // Obtener IDs de empleados que YA tienen usuario
        $employeesWithUser = \App\Models\User::whereNotNull('employee_id')
            ->pluck('employee_id')
            ->toArray();
        
        \Illuminate\Support\Facades\Log::info('Empleados con usuario: ' . json_encode($employeesWithUser));
        
        // Filtrar empleados que NO tienen usuario
        $availableEmployees = \App\Models\Employee::where('active', true)
            ->whereNotIn('id', $employeesWithUser)
            ->with('position')
            ->orderBy('firstname')
            ->get();
        
        \Illuminate\Support\Facades\Log::info('Empleados disponibles: ' . $availableEmployees->count());
        
        return $availableEmployees;
    }
}