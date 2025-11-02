<?php

namespace App\Services;

use App\Models\StaffSchedule;
use App\Models\ScheduleTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
                $query->whereNull('specific_date');
            } else {
                $query->whereNotNull('specific_date');
            }
        }

        if (!empty($filters['date'])) {
            $date = Carbon::parse($filters['date']);
            $query->where(function ($q) use ($date) {
                // Asignaciones específicas para esa fecha
                $q->where('specific_date', $date)
                  // O asignaciones recurrentes vigentes
                  ->orWhere(function ($sq) use ($date) {
                      $sq->whereNull('specific_date')
                         ->where(function ($dateQ) use ($date) {
                             $dateQ->whereNull('start_date')
                                   ->orWhere('start_date', '<=', $date);
                         })
                         ->where(function ($dateQ) use ($date) {
                             $dateQ->whereNull('end_date')
                                   ->orWhere('end_date', '>=', $date);
                         });
                  });
            });
        }

        $pagination = $filters['paginate'] ?? 15;

        return $query->with([
            'staff.position',
            'scheduleTemplate.scheduleDays',
            'cubicle',
            'originalStaff'
        ])
            ->orderBy('start_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->simplePaginate($pagination);
    }

    public function getStaffScheduleById($id)
    {
        return StaffSchedule::with([
            'staff.position',
            'scheduleTemplate.scheduleDays',
            'cubicle',
            'originalStaff'
        ])->findOrFail($id);
    }

    public function createStaffSchedule(array $data)
    {
        return DB::transaction(function () use ($data) {
            Log::info('Creating staff schedule', ['data' => $data]);

            // Validar conflictos
            $this->validateScheduleConflict($data);

            // Preparar datos
            $scheduleData = [
                'staff_id' => $data['staff_id'],
                'schedule_template_id' => $data['schedule_template_id'],
                'selected_days' => $data['selected_days'] ?? null,
                'cubicle_id' => $data['cubicle_id'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'specific_date' => $data['specific_date'] ?? null,
                'specific_start_time' => $data['specific_start_time'] ?? null,
                'specific_end_time' => $data['specific_end_time'] ?? null,
                'is_override' => $data['is_override'] ?? false,
                'original_staff_id' => $data['original_staff_id'] ?? null,
                'status' => $data['status'] ?? 'active',
                'notes' => $data['notes'] ?? null,
            ];

            $staffSchedule = StaffSchedule::create($scheduleData);

            return $staffSchedule->load([
                'staff.position',
                'scheduleTemplate.scheduleDays',
                'cubicle',
                'originalStaff'
            ]);
        });
    }

    public function updateStaffSchedule($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $staffSchedule = StaffSchedule::findOrFail($id);

            // Validar conflictos si se cambian datos relevantes
            if (isset($data['staff_id']) || isset($data['schedule_template_id']) || 
                isset($data['start_date']) || isset($data['selected_days']) ||
                isset($data['specific_date'])) {
                $this->validateScheduleConflict($data, $id);
            }

            $staffSchedule->update($data);
            
            return $staffSchedule->load([
                'staff.position',
                'scheduleTemplate.scheduleDays',
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
     * ✅ Validación mejorada de conflictos
     */
    protected function validateScheduleConflict(array $data, $excludeId = null)
    {
        $query = StaffSchedule::where('staff_id', $data['staff_id'])
            ->where('status', 'active');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // Si es asignación específica
        if (!empty($data['specific_date'])) {
            $query->where(function ($q) use ($data) {
                // Conflicto con otra asignación específica el mismo día
                $q->where('specific_date', $data['specific_date'])
                  // O con asignación recurrente que aplique ese día
                  ->orWhere(function ($sq) use ($data) {
                      $specificDate = Carbon::parse($data['specific_date']);
                      $dayOfWeek = $specificDate->dayOfWeekIso;
                      
                      $sq->whereNull('specific_date')
                         ->where(function ($dateQ) use ($specificDate) {
                             $dateQ->whereNull('start_date')
                                   ->orWhere('start_date', '<=', $specificDate);
                         })
                         ->where(function ($dateQ) use ($specificDate) {
                             $dateQ->whereNull('end_date')
                                   ->orWhere('end_date', '>=', $specificDate);
                         })
                         ->where(function ($dayQ) use ($dayOfWeek) {
                             $dayQ->whereNull('selected_days')
                                  ->orWhereJsonContains('selected_days', $dayOfWeek);
                         });
                  });
            });
        }
        // Si es asignación recurrente
        else {
            $templateId = $data['schedule_template_id'];
            $selectedDays = $data['selected_days'] ?? null;
            
            $query->where('schedule_template_id', $templateId)
                  ->where(function ($q) use ($selectedDays) {
                      // Conflicto si los días se solapan
                      if ($selectedDays && !empty($selectedDays)) {
                          $q->whereNull('selected_days')
                            ->orWhere(function ($sq) use ($selectedDays) {
                                foreach ($selectedDays as $day) {
                                    $sq->orWhereJsonContains('selected_days', $day);
                                }
                            });
                      }
                  });

            // Verificar solapamiento de fechas
            if (!empty($data['start_date'])) {
                $query->where(function ($q) use ($data) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', $data['start_date']);
                });
            }
            if (!empty($data['end_date'])) {
                $query->where(function ($q) use ($data) {
                    $q->whereNull('start_date')
                      ->orWhere('start_date', '<=', $data['end_date']);
                });
            }
        }

        if ($query->exists()) {
            throw new \Exception('Ya existe una asignación de horario para este personal en este período.');
        }
    }

    /**
     * ✅ Obtiene el horario semanal mejorado
     */
    public function getWeeklyScheduleForStaff($staffId, $startDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate) : Carbon::now()->startOfWeek();
        $endDate = $startDate->copy()->endOfWeek();

        $schedules = StaffSchedule::where('staff_id', $staffId)
            ->where('status', 'active')
            ->with(['scheduleTemplate.scheduleDays', 'cubicle'])
            ->where(function ($query) use ($startDate, $endDate) {
                // Asignaciones específicas en la semana
                $query->whereBetween('specific_date', [$startDate, $endDate])
                      // O asignaciones recurrentes vigentes
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->whereNull('specific_date')
                            ->where(function ($dateQ) use ($endDate) {
                                $dateQ->whereNull('start_date')
                                      ->orWhere('start_date', '<=', $endDate);
                            })
                            ->where(function ($dateQ) use ($startDate) {
                                $dateQ->whereNull('end_date')
                                      ->orWhere('end_date', '>=', $startDate);
                            });
                      });
            })
            ->get();

        // Construir calendario semanal
        $calendar = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $daySchedules = $schedules->filter(function ($schedule) use ($date) {
                return $schedule->appliesOnDate($date);
            })->map(function ($schedule) use ($date) {
                $timeInfo = $schedule->getScheduleForDate($date);
                return [
                    'id' => $schedule->id,
                    'template_name' => $schedule->scheduleTemplate->name,
                    'start_time' => $timeInfo['start_time'],
                    'end_time' => $timeInfo['end_time'],
                    'cubicle' => $schedule->cubicle?->name,
                    'is_override' => $schedule->is_override,
                ];
            });

            $calendar[] = [
                'date' => $date->toDateString(),
                'day_name' => $date->locale('es')->dayName,
                'schedules' => $daySchedules->values(),
            ];
        }

        return $calendar;
    }
}