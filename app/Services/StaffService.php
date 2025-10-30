<?php

namespace App\Services;

use App\Models\Staff;
use Illuminate\Support\Facades\DB;

class StaffService
{
    public function getAllStaff(array $filters = [])
    {
        $query = Staff::query();

        if (isset($filters['is_active'])) {
            $active = $filters['is_active'] === 'true' || $filters['is_active'] === '1';
            $query->where('is_active', $active);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'ILIKE', "%{$filters['search']}%")
                    ->orWhere('last_name', 'ILIKE', "%{$filters['search']}%")
                    ->orWhere('email', 'ILIKE', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['position_id'])) {
            $query->where('position_id', $filters['position_id']);
        }

        $pagination = $filters['paginate'] ?? 15;

        return $query->with(['position'])
            ->orderBy('first_name')
            ->simplePaginate($pagination);
    }

    public function getStaffById($id)
    {
        return Staff::with(['position', 'staffSchedules.scheduleDay.scheduleTemplate', 'staffSchedules.cubicle'])
            ->findOrFail($id);
    }

    public function createStaff(array $data)
    {
        return DB::transaction(function () use ($data) {
            return Staff::create($data);
        });
    }

    public function updateStaff($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $staff = Staff::findOrFail($id);
            $staff->update($data);
            return $staff;
        });
    }

    public function deleteStaff($id)
    {
        return DB::transaction(function () use ($id) {
            $staff = Staff::findOrFail($id);
            $staff->delete();
            return true;
        });
    }
}