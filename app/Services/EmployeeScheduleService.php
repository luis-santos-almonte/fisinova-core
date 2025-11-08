<?php

namespace App\Services;

use App\Models\EmployeeSchedule;
use Illuminate\Support\Facades\DB;

class EmployeeScheduleService
{
    public function getAllSchedules(array $filters = [])
    {
        $query = EmployeeSchedule::with(['employee', 'scheduleTemplate.scheduleDays', 'cubicle']);

        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('firstname', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('lastname', 'like', '%' . $filters['search'] . '%');
            });
        }

        $perPage = $filters['paginate'] ?? 15;
        return $query->paginate($perPage);
    }

    public function createSchedule(array $data)
    {
        return EmployeeSchedule::create($data);
    }

    public function updateSchedule(int $id, array $data)
    {
        $schedule = EmployeeSchedule::findOrFail($id);
        $schedule->update($data);
        return $schedule;
    }

    public function deleteSchedule(int $id)
    {
        $schedule = EmployeeSchedule::findOrFail($id);
        $schedule->delete();
        return true;
    }
}