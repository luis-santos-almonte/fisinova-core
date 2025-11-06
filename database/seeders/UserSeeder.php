<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\Position;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar que existan roles
        $roles = [
            'admin' => Role::where('name', 'admin')->first(),
            'medic' => Role::where('name', 'medic')->first(),
            'therapist' => Role::where('name', 'therapist')->first(),
            'secretary' => Role::where('name', 'secretary')->first(),
        ];

        // Verificar roles
        foreach ($roles as $key => $role) {
            if (!$role) {
                $this->command->error("❌ Falta el rol: {$key}");
                $this->command->error('Por favor ejecute: php artisan db:seed --class=RoleSeeder');
                return;
            }
        }

        // Verificar que existan positions
        $positions = [
            'admin' => Position::where('name', 'Administrador')->first(),
            'medic' => Position::where('name', 'Médico')->first(),
            'therapist' => Position::where('name', 'Terapista')->first(),
            'secretary' => Position::where('name', 'Secretaria')->first(),
        ];

        // Verificar positions
        foreach ($positions as $key => $position) {
            if (!$position) {
                $this->command->error("❌ Falta la posición: {$key}");
                $this->command->error('Por favor ejecute: php artisan db:seed --class=PositionSeeder');
                return;
            }
        }

        $users = [
            // 1. ADMINISTRADOR
            [
                'user' => [
                    'name' => 'Admin',
                    'email' => 'admin@clinica.com',
                    'password' => Hash::make('admin123'),
                ],
                'employee' => [
                    'position_id' => $positions['admin']->id,
                    'firstname' => 'Carlos',
                    'lastname' => 'Administrador',
                    'dni' => '00100000001',
                    'email' => 'admin@clinica.com',
                    'cellphone' => '809-555-0001',
                    'phone' => '809-555-0001',
                    'address' => 'Santo Domingo, DN',
                    'active' => true,
                ],
                'roles' => ['admin'],
            ],
            
            // 2. MÉDICOS
            [
                'user' => [
                    'name' => 'JPEREZ',
                    'email' => 'juan.perez@clinica.com',
                    'password' => Hash::make('medico123'),
                ],
                'employee' => [
                    'position_id' => $positions['medic']->id,
                    'firstname' => 'Juan',
                    'lastname' => 'Pérez García',
                    'dni' => '00200000001',
                    'email' => 'juan.perez@clinica.com',
                    'cellphone' => '809-555-1001',
                    'phone' => '809-555-1001',
                    'address' => 'Santo Domingo, DN',
                    'active' => true,
                ],
                'roles' => ['medic'],
            ],
            [
                'user' => [
                    'name' => 'MLOPEZ',
                    'email' => 'maria.lopez@clinica.com',
                    'password' => Hash::make('medico123'),
                ],
                'employee' => [
                    'position_id' => $positions['medic']->id,
                    'firstname' => 'María',
                    'lastname' => 'López Martínez',
                    'dni' => '00200000002',
                    'email' => 'maria.lopez@clinica.com',
                    'cellphone' => '809-555-1002',
                    'phone' => '809-555-1002',
                    'address' => 'Santo Domingo, DN',
                    'active' => true,
                ],
                'roles' => ['medic'],
            ],
            
            // 3. TERAPISTAS
            [
                'user' => [
                    'name' => 'YLOPEZ',
                    'email' => 'yesenia.lopez@clinica.com',
                    'password' => Hash::make('terapista123'),
                ],
                'employee' => [
                    'position_id' => $positions['therapist']->id,
                    'firstname' => 'Yesenia',
                    'lastname' => 'López Ramírez',
                    'dni' => '40125849858',
                    'email' => 'yesenia.lopez@clinica.com',
                    'cellphone' => '829-555-2001',
                    'phone' => '809-555-2001',
                    'address' => 'Santo Domingo, DN',
                    'active' => true,
                ],
                'roles' => ['therapist'],
            ],
            [
                'user' => [
                    'name' => 'PSANCHEZ',
                    'email' => 'pedro.sanchez@clinica.com',
                    'password' => Hash::make('terapista123'),
                ],
                'employee' => [
                    'position_id' => $positions['therapist']->id,
                    'firstname' => 'Pedro',
                    'lastname' => 'Sánchez Díaz',
                    'dni' => '00300000001',
                    'email' => 'pedro.sanchez@clinica.com',
                    'cellphone' => '829-555-2002',
                    'phone' => '809-555-2002',
                    'address' => 'Santo Domingo, DN',
                    'active' => true,
                ],
                'roles' => ['therapist'],
            ],
            
            // 4. SECRETARIAS
            [
                'user' => [
                    'name' => 'AGOMEZ',
                    'email' => 'ana.gomez@clinica.com',
                    'password' => Hash::make('secretaria123'),
                ],
                'employee' => [
                    'position_id' => $positions['secretary']->id,
                    'firstname' => 'Ana',
                    'lastname' => 'Gómez Fernández',
                    'dni' => '00400000001',
                    'email' => 'ana.gomez@clinica.com',
                    'cellphone' => '849-555-3001',
                    'phone' => '809-555-3001',
                    'address' => 'Santo Domingo, DN',
                    'active' => true,
                ],
                'roles' => ['secretary'],
            ],
            [
                'user' => [
                    'name' => 'CROGRIGUEZ',
                    'email' => 'carmen.rodriguez@clinica.com',
                    'password' => Hash::make('secretaria123'),
                ],
                'employee' => [
                    'position_id' => $positions['secretary']->id,
                    'firstname' => 'Carmen',
                    'lastname' => 'Rodríguez Santos',
                    'dni' => '00400000002',
                    'email' => 'carmen.rodriguez@clinica.com',
                    'cellphone' => '849-555-3002',
                    'phone' => '809-555-3002',
                    'address' => 'Santo Domingo, DN',
                    'active' => true,
                ],
                'roles' => ['secretary'],
            ],
        ];

        foreach ($users as $userData) {
            // Crear usuario
            $user = User::updateOrCreate(
                ['email' => $userData['user']['email']],
                $userData['user']
            );

            // Crear empleado
            $employeeData = $userData['employee'];
            $employeeData['user_id'] = $user->id;
            
            Employee::updateOrCreate(
                ['user_id' => $user->id],
                $employeeData
            );

            // Asignar roles
            $roleIds = [];
            foreach ($userData['roles'] as $roleName) {
                if (isset($roles[$roleName])) {
                    $roleIds[$roles[$roleName]->id] = ['active' => true];
                }
            }
            $user->roles()->syncWithoutDetaching($roleIds);
        }

        $this->command->info('✅ Usuarios creados: 7 usuarios del sistema');
        $this->command->info('   📧 Emails: admin@clinica.com, juan.perez@clinica.com, etc.');
        $this->command->info('   🔑 Contraseñas: admin123, medico123, terapista123, secretaria123');
    }
}