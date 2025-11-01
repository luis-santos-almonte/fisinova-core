<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class StaffService
{
    /**
     * ✅ CAMBIO: usar Employee
     */
    public function getAllStaff(array $filters = [])
    {
        $query = Employee::query();

        if (isset($filters['is_active'])) {
            $active = $filters['is_active'] === 'true' || $filters['is_active'] === '1';
            $query->where('active', $active);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('firstname', 'ILIKE', "%{$filters['search']}%")
                    ->orWhere('lastname', 'ILIKE', "%{$filters['search']}%")
                    ->orWhere('email', 'ILIKE', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['position_id'])) {
            $query->where('position_id', $filters['position_id']);
        }

        $pagination = $filters['paginate'] ?? 15;

        return $query->with(['position'])
            ->orderBy('firstname')
            ->simplePaginate($pagination);
    }

    /**
     * ✅ CAMBIO: usar Employee
     */
    public function getStaffById($id)
    {
        return Employee::with(['position', 'staffSchedules.scheduleDay.scheduleTemplate', 'staffSchedules.cubicle'])
            ->findOrFail($id);
    }

    /**
     * ✅ CAMBIO: usar Employee
     */
    public function createStaff(array $data)
    {
        return DB::transaction(function () use ($data) {
            return Employee::create($data);
        });
    }

    /**
     * ✅ CAMBIO: usar Employee
     */
    public function updateStaff($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $staff = Employee::findOrFail($id);
            $staff->update($data);
            return $staff;
        });
    }

    /**
     * ✅ CAMBIO: usar Employee
     */
    public function deleteStaff($id)
    {
        return DB::transaction(function () use ($id) {
            $staff = Employee::findOrFail($id);
            $staff->delete();
            return true;
        });
    }
}