<?php

namespace App\Services;

use App\Models\StaffSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StaffScheduleService
{
    public function getAllStaffSchedules(array $filters = [])
    {
        $query = StaffSchedule::query();

        if (!empty($filters['staff_id'])) {
            $query->where('staff_id', $filters['staff_id']);
        }

        if (!empty($filters['cubicle_id'])) {
            $query->where('cubicle_id', $filters['cubicle_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['is_recurring'])) {
            $isRecurring = $filters['is_recurring'] === 'true' || $filters['is_recurring'] === '1';
            if ($isRecurring) {
                $query->whereNull('assignment_date');
            } else {
                $query->whereNotNull('assignment_date');
            }
        }

        if (!empty($filters['date'])) {
            $query->where('assignment_date', $filters['date']);
        }

        $pagination = $filters['paginate'] ?? 15;

        return $query->with([
            'staff.position',
            'scheduleDay.scheduleTemplate',
            'cubicle',
            'originalStaff'
        ])
            ->orderBy('assignment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->simplePaginate($pagination);
    }

    public function getStaffScheduleById($id)
    {
        return StaffSchedule::with([
            'staff.position',
            'scheduleDay.scheduleTemplate',
            'cubicle',
            'originalStaff'
        ])->findOrFail($id);
    }

    public function createStaffSchedule(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Log para debugging
            Log::info('Creating staff schedule', ['data' => $data]);

            // Validar que no haya conflictos de horario
            $this->validateScheduleConflict($data);

            // Crear la asignación
            $staffSchedule = StaffSchedule::create([
                'staff_id' => $data['staff_id'],
                'schedule_day_id' => $data['schedule_day_id'],
                'cubicle_id' => $data['cubicle_id'] ?? null,
                'assignment_date' => $data['assignment_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'is_override' => $data['is_override'] ?? false,
                'original_staff_id' => $data['original_staff_id'] ?? null,
                'status' => $data['status'] ?? 'active',
                'notes' => $data['notes'] ?? null,
            ]);

            // Cargar relaciones
            return $staffSchedule->load([
                'staff.position',
                'scheduleDay.scheduleTemplate',
                'cubicle',
                'originalStaff'
            ]);
        });
    }

    public function updateStaffSchedule($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $staffSchedule = StaffSchedule::findOrFail($id);

            // Validar que no haya conflictos de horario si se cambian datos relevantes
            if (isset($data['staff_id']) || isset($data['schedule_day_id']) || 
                isset($data['assignment_date']) || isset($data['cubicle_id'])) {
                $this->validateScheduleConflict($data, $id);
            }

            $staffSchedule->update($data);
            
            return $staffSchedule->load([
                'staff.position',
                'scheduleDay.scheduleTemplate',
                'cubicle',
                'originalStaff'
            ]);
        });
    }

    public function deleteStaffSchedule($id)
    {
        return DB::transaction(function () use ($id) {
            $staffSchedule = StaffSchedule::findOrFail($id);
            $staffSchedule->delete();
            return true;
        });
    }

    /**
     * Valida que no existan conflictos de horario para el personal
     */
    protected function validateScheduleConflict(array $data, $excludeId = null)
    {
        $query = StaffSchedule::where('staff_id', $data['staff_id'])
            ->where('schedule_day_id', $data['schedule_day_id'])
            ->where('status', 'active');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // Si es un horario específico, validar la fecha
        if (isset($data['assignment_date'])) {
            $query->where(function ($q) use ($data) {
                $q->where('assignment_date', $data['assignment_date'])
                    ->orWhereNull('assignment_date'); // También verificar horarios recurrentes
            });
        } else {
            // Si es recurrente, verificar que no exista otro recurrente
            $query->whereNull('assignment_date');
        }

        if ($query->exists()) {
            throw new \Exception('Ya existe una asignación de horario para este personal en este día y hora.');
        }
    }

    /**
     * Obtiene el horario semanal de un personal específico
     */
    public function getWeeklyScheduleForStaff($staffId, $startDate = null)
    {
        $query = StaffSchedule::where('staff_id', $staffId)
            ->where('status', 'active')
            ->with(['scheduleDay.scheduleTemplate', 'cubicle']);

        if ($startDate) {
            $endDate = date('Y-m-d', strtotime($startDate . ' +7 days'));
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereNull('assignment_date')
                    ->orWhereBetween('assignment_date', [$startDate, $endDate]);
            });
        }

        return $query->get();
    }
}