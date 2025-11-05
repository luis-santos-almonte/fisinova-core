<?php

namespace App\Services;

use App\Models\Employee;
use App\Validators\Employee\EmployeeFilterValidator;

class EmployeeService
{
    public function getAllEmployees(array $filters = [])
    {
        $query = Employee::query();

        // Filtro por estado activo
        if (isset($filters['active'])) {
            $active = $filters['active'] === 'true' || $filters['active'] === '1';
            $query->where('active', $active);
        }

        // Filtro de búsqueda por texto
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('firstname', 'ILIKE', "%{$filters['search']}%")
                    ->orWhere('lastname', 'ILIKE', "%{$filters['search']}%")
                    ->orWhere('dni', 'ILIKE', "%{$filters['search']}%");
            });
        }

        // Filtro por position_id específico
        if (!empty($filters['position_id'])) {
            $query->where('position_id', $filters['position_id']);
        }

        // Filtro por tipo médico (Médico=1, Terapista=2)
        if (!empty($filters['type']) && $filters['type'] === 'medical') {
            $query->whereIn('position_id', [1, 2]); // Médico y Terapista
        }

        $pagination = $filters['paginate'] ?? 15;

        return $query->with(['position'])
            ->orderBy('firstname')
            ->simplePaginate($pagination);
    }

    public function getEmployeeById($id)
    {
        return Employee::with(['user', 'position', 'schedules'])->findOrFail($id);
    }

    public function getMedics()
    {
        return Employee::whereIn('position_id', [1]) // Médico
            ->where('active', true)
            ->with(['position'])
            ->orderBy('firstname')
            ->get();
    }

    public function getTherapists()
    {
        return Employee::whereIn('position_id', [2]) // Terapista
            ->where('active', true)
            ->with(['position'])
            ->orderBy('firstname')
            ->get();
    }

    public function createEmployee(array $data)
    {
        return Employee::create($data);
    }

    public function updateEmployee($id, array $data)
    {
        $employee = Employee::findOrFail($id);
        $employee->update($data);
        return $employee;
    }

    public function deleteEmployee($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        return true;
    }
}
