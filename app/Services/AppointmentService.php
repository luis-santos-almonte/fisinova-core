<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\EmployeeSchedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Log;

class AppointmentService
{
    /**
     * Obtener todas las citas con filtros
     */
    public function getAllAppointments(array $filters = [])
    {
        $query = Appointment::query();

        // Filtro de activo
        if (isset($filters['active'])) {
            $query->where('active', filter_var($filters['active'], FILTER_VALIDATE_BOOLEAN));
        }

        // Filtro de rango de fechas
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereDate('appointment_date', '>=', $filters['start_date'])
                ->whereDate('appointment_date', '<=', $filters['end_date']);
        }

        // Filtro de empleado
        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        // Filtro de paciente
        if (!empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }

        // Filtro de estado
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $pagination = $filters['paginate'] ?? 15;

        return $query->with(['employee', 'patient', 'insurance'])
            ->orderBy('appointment_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->simplePaginate($pagination);
    }

    /**
     * Obtener cita por ID
     */
    public function getAppointmentById(int $id)
    {
        return Appointment::with([
            'employee',
            'patient',
            'insurance',
            'procedures'
        ])->findOrFail($id);
    }

    /**
     * Crear nueva cita
     */
    public function createAppointment(array $data)
    {
        return Appointment::create($data);
    }

    /**
     * Actualizar cita existente
     */
    public function updateAppointment(int $id, array $data)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update($data);
        return $appointment->fresh(['employee', 'patient', 'insurance']);
    }

    /**
     * Eliminar cita
     */
    public function deleteAppointment(int $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();
        return true;
    }

    /**
     * Obtener disponibilidad del doctor
     */
    public function getDoctorAvailability(
        int $employeeId,
        string $startDate,
        string $endDate,
        int $duration = 60
    ): array {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Obtener horarios del empleado
        $schedules = EmployeeSchedule::where('employee_id', $employeeId)
            ->where('status', 'active')
            ->with(['scheduleTemplate.scheduleDays', 'cubicle'])
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('specific_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->whereNull('specific_date')
                            ->where(function ($dateQ) use ($end) {
                                $dateQ->whereNull('start_date')
                                    ->orWhere('start_date', '<=', $end);
                            })
                            ->where(function ($dateQ) use ($start) {
                                $dateQ->whereNull('end_date')
                                    ->orWhere('end_date', '>=', $start);
                            });
                    });
            })
            ->get();

        // Obtener citas existentes
        $appointments = Appointment::where('employee_id', $employeeId)
            ->whereIn('status', ['programada', 'confirmada'])
            ->whereBetween('appointment_date', [$start, $end])
            ->select('appointment_date', 'start_time', 'end_time')
            ->get();

        $availability = [];
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $date) {
            $dayAvailability = $this->buildDayAvailability(
                $date,
                $schedules,
                $appointments,
                $duration
            );

            if (!empty($dayAvailability['slots'])) {
                $availability[] = $dayAvailability;
            }
        }

        return [
            'doctor_id' => $employeeId,
            'date_range' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
            'duration' => $duration,
            'days' => $availability,
            'total_available_slots' => collect($availability)->sum('available_count'),
        ];
    }

    /**
     * Construir disponibilidad para un día específico
     */
    private function buildDayAvailability(
        Carbon $date,
        $schedules,
        $appointments,
        int $duration
    ): array {
        $daySlots = [];

        foreach ($schedules as $schedule) {
            // Verificar si el horario aplica para esta fecha
            if (!$schedule->appliesOnDate($date)) {
                continue;
            }

            $timeInfo = $schedule->getScheduleForDate($date);

            if (empty($timeInfo['start_time']) || empty($timeInfo['end_time'])) {
                continue;
            }

            // Generar slots disponibles
            $workStart = Carbon::parse($date->format('Y-m-d') . ' ' . $timeInfo['start_time']);
            $workEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $timeInfo['end_time']);

            $currentSlot = $workStart->copy();

            while ($currentSlot->copy()->addMinutes($duration)->lte($workEnd)) {
                $slotEnd = $currentSlot->copy()->addMinutes($duration);

                // Verificar si el slot está ocupado
                $isAvailable = !$this->isSlotOccupied(
                    $appointments,
                    $date,
                    $currentSlot,
                    $slotEnd
                );

                $daySlots[] = [
                    'start_time' => $currentSlot->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'display' => $currentSlot->format('h:i A') . ' - ' . $slotEnd->format('h:i A'),
                    'is_available' => $isAvailable,
                    'cubicle' => $schedule->cubicle?->name,
                ];

                $currentSlot->addMinutes($duration);
            }
        }

        $availableCount = collect($daySlots)->where('is_available', true)->count();

        return [
            'date' => $date->toDateString(),
            'day_name' => $date->locale('es')->dayName,
            'day_of_week' => $date->dayOfWeek,
            'slots' => $daySlots,
            'available_count' => $availableCount,
            'total_count' => count($daySlots),
        ];
    }

    /**
     * Verificar si un slot está ocupado
     */
    private function isSlotOccupied($appointments, Carbon $date, Carbon $slotStart, Carbon $slotEnd): bool
    {
        $dateStr = $date->toDateString();

        foreach ($appointments as $appointment) {
            if ($appointment->appointment_date !== $dateStr) {
                continue;
            }

            $appointmentStart = Carbon::parse($dateStr . ' ' . $appointment->start_time);
            $appointmentEnd = Carbon::parse($dateStr . ' ' . $appointment->end_time);

            // Verificar solapamiento
            if ($slotStart->lt($appointmentEnd) && $slotEnd->gt($appointmentStart)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validar slot de tiempo
     */
    public function validateTimeSlot(
        int $employeeId,
        string $date,
        string $time,
        int $duration = 60,
        ?int $excludeAppointmentId = null
    ): array {
        $dateCarbon = Carbon::parse($date);
        $startTime = Carbon::parse($date . ' ' . $time);
        $endTime = $startTime->copy()->addMinutes($duration);

        // Verificar citas conflictivas
        $conflictingAppointment = Appointment::where('employee_id', $employeeId)
            ->where('appointment_date', $date)
            ->whereIn('status', ['programada', 'confirmada'])
            ->when($excludeAppointmentId, function ($query) use ($excludeAppointmentId) {
                $query->where('id', '!=', $excludeAppointmentId);
            })
            ->where(function ($query) use ($time, $endTime) {
                $query->where(function ($q) use ($time, $endTime) {
                    $q->where('start_time', '<', $endTime->format('H:i'))
                        ->where('end_time', '>', $time);
                });
            })
            ->with('patient')
            ->first();

        if ($conflictingAppointment) {
            return [
                'is_available' => false,
                'reason' => 'conflicting_appointment',
                'message' => 'El horario se solapa con otra cita existente',
                'conflicting_appointment' => [
                    'id' => $conflictingAppointment->id,
                    'time' => $conflictingAppointment->start_time . ' - ' . $conflictingAppointment->end_time,
                    'patient' => $conflictingAppointment->patient
                        ? $conflictingAppointment->patient->firstname . ' ' . $conflictingAppointment->patient->lastname
                        : 'Sin asignar',
                ],
            ];
        }

        return [
            'is_available' => true,
            'reason' => null,
            'message' => 'El horario está disponible',
        ];
    }

    /**
     * Obtener siguiente slot disponible
     */
    public function getNextAvailableSlot(
        int $employeeId,
        ?string $fromDate = null,
        int $duration = 60
    ): ?array {
        $startDate = $fromDate ? Carbon::parse($fromDate) : Carbon::now();
        $endDate = $startDate->copy()->addDays(30);

        $availability = $this->getDoctorAvailability(
            $employeeId,
            $startDate->toDateString(),
            $endDate->toDateString(),
            $duration
        );

        foreach ($availability['days'] as $day) {
            foreach ($day['slots'] as $slot) {
                if ($slot['is_available']) {
                    return [
                        'date' => $day['date'],
                        'time' => $slot['start_time'],
                        'end_time' => $slot['end_time'],
                        'display' => Carbon::parse($day['date'])->locale('es')->isoFormat('dddd, D [de] MMMM') .
                            ' - ' . $slot['display'],
                    ];
                }
            }
        }

        return null;
    }
}
