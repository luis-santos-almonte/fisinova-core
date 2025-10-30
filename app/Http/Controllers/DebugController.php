<?php
// app/Http/Controllers/DebugController.php
// ARCHIVO TEMPORAL PARA DEBUG - ELIMINAR DESPUÉS

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Traits\ApiResponse;

class DebugController extends Controller
{
    use ApiResponse;

    public function debugEmployees()
    {
        // 1. Verificar estructura de tabla users
        $userTableColumns = \DB::select("SELECT column_name FROM information_schema.columns WHERE table_name='users'");
        
        // 2. Total de empleados
        $totalEmployees = Employee::count();
        
        // 3. Empleados activos
        $activeEmployees = Employee::where('active', true)->count();
        
        // 4. Usuarios con employee_id
        $usersWithEmployee = User::whereNotNull('employee_id')->get(['id', 'name', 'employee_id']);
        
        // 5. Empleados sin usuario (método whereDoesntHave)
        $employeesWithoutUserMethod1 = Employee::whereDoesntHave('user')
            ->where('active', true)
            ->with('position')
            ->get();
        
        // 6. Empleados sin usuario (método whereNotIn)
        $employeesWithUser = User::whereNotNull('employee_id')->pluck('employee_id')->toArray();
        $employeesWithoutUserMethod2 = Employee::where('active', true)
            ->whereNotIn('id', $employeesWithUser)
            ->with('position')
            ->get();
        
        // 7. Todos los empleados activos con info de usuario
        $allActiveEmployees = Employee::where('active', true)
            ->with(['position', 'user'])
            ->get()
            ->map(function($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->firstname . ' ' . $emp->lastname,
                    'position' => $emp->position?->name,
                    'has_user' => $emp->user ? true : false,
                    'user_name' => $emp->user?->name,
                ];
            });

        return $this->successResponse([
            'debug_info' => [
                'user_table_has_employee_id' => collect($userTableColumns)->contains(function($col) {
                    return $col->column_name === 'employee_id';
                }),
                'total_employees' => $totalEmployees,
                'active_employees' => $activeEmployees,
                'users_with_employee' => $usersWithEmployee,
                'employees_without_user_method1' => $employeesWithoutUserMethod1->count(),
                'employees_without_user_method2' => $employeesWithoutUserMethod2->count(),
                'method1_data' => $employeesWithoutUserMethod1,
                'method2_data' => $employeesWithoutUserMethod2,
                'all_active_employees' => $allActiveEmployees,
            ]
        ]);
    }
}