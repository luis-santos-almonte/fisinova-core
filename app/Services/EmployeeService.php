<?php

namespace App\Services;

use App\Models\Employee;
use App\Validators\Employee\EmployeeFilterValidator;

class EmployeeService
{
    public function getAllEmployees(array $filters = [])
    {
        $filters = EmployeeFilterValidator::validate($filters);

        $pagination = $filters['paginate'] ?? 10;
        $query = Employee::query();

        if (isset($filters['active'])) {
            $query->active($filters['active'] === 'true' || $filters['active'] === 1);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }

        if (!empty($filters['dni'])) {
            $query->where('dni', $filters['dni']);
        }

        if (!empty($filters['name'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('firstname', 'ILIKE', "%{$filters['name']}%")
                    ->orWhere('lastname', 'ILIKE', "%{$filters['name']}%");
            });
        }

        if (!empty($filters['email'])) {
            $query->where('email', 'ILIKE', "%{$filters['email']}%");
        }

        if (!empty($filters['position_id'])) {
            $query->where('position_id', $filters['position_id']);
        }

        return $query->with(['user', 'position'])->simplePaginate($pagination);
    }

    public function getEmployeeById($id)
    {
        return Employee::with(['user', 'position', 'schedules'])->findOrFail($id);
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
