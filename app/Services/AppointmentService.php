<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\StaffSchedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AppointmentService
{
    public function getAllAppointments(array $filters = [])
    {
        $query = Appointment::query();


        if (isset($filters['active'])) {
            $active = $filters['active'] === 'true' || $filters['active'] === '1';
            $query->where('active', $active);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('appointment_date', [$filters['start_date'], $filters['end_date']]);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $pagination = $filters['paginate'] ?? 15;

        return $query->with(['employee', 'patient', 'insurance'])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->simplePaginate($pagination);
    }

    public function getAppointmentById($id)
    {
        return Appointment::with(['employee', 'patient', 'insurance', 'procedures'])
            ->findOrFail($id);
    }

    public function createAppointment(array $data)
    {
        return Appointment::create($data);
    }

    public function updateAppointment($id, array $data)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update($data);
        return $appointment;
    }

    public function deleteAppointment($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();
        return true;
    }

    public function getDoctorAvailability(
        int $employeeId,
        string $startDate,
        string $endDate,
        int $duration = 60
    ): array {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Obtener horarios del empleado
        $schedules = StaffSchedule::where('staff_id', $employeeId)
            ->where('status', 'active')
            ->with('scheduleTemplate.scheduleDays', 'cubicle')
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

        return $availability;
    }

    /**
     * ✅ NUEVO: Construye la disponibilidad para un día específico
     */
    private function buildDayAvailability(
        Carbon $date,
        $schedules,
        $appointments,
        int $duration
    ): array {
        $daySlots = [];

        foreach ($schedules as $schedule) {
            if (!$schedule->appliesOnDate($date)) {
                continue;
            }

            $timeInfo = $schedule->getScheduleForDate($date);

            if (!$timeInfo['start_time'] || !$timeInfo['end_time']) {
                continue;
            }

            $workStart = Carbon::parse($date->format('Y-m-d') . ' ' . $timeInfo['start_time']);
            $workEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $timeInfo['end_time']);

            $slots = $this->generateTimeSlots($workStart, $workEnd, $duration);

            foreach ($slots as &$slot) {
                $slot['is_available'] = !$this->isSlotOccupied(
                    $slot['start'],
                    $slot['end'],
                    $appointments,
                    $date
                );
                $slot['cubicle'] = $schedule->cubicle?->name;
            }

            $daySlots = array_merge($daySlots, $slots);
        }

        usort($daySlots, function ($a, $b) {
            return $a['start']->timestamp <=> $b['start']->timestamp;
        });

        return [
            'date' => $date->format('Y-m-d'),
            'day_name' => ucfirst($date->locale('es')->dayName),
            'day_of_week' => $date->dayOfWeekIso,
            'slots' => $daySlots,
            'available_count' => count(array_filter($daySlots, fn($s) => $s['is_available'])),
            'total_count' => count($daySlots),
        ];
    }

    /**
     * ✅ NUEVO: Genera slots de tiempo para un rango de horas
     */
    private function generateTimeSlots(Carbon $start, Carbon $end, int $duration): array
    {
        $slots = [];
        $current = $start->copy();

        while ($current->copy()->addMinutes($duration)->lte($end)) {
            $slotEnd = $current->copy()->addMinutes($duration);

            $slots[] = [
                'start' => $current->copy(),
                'end' => $slotEnd->copy(),
                'start_time' => $current->format('H:i'),
                'end_time' => $slotEnd->format('H:i'),
                'display' => $current->format('H:i') . ' - ' . $slotEnd->format('H:i'),
                'is_available' => true,
            ];

            $current->addMinutes($duration);
        }

        return $slots;
    }

    /**
     * ✅ NUEVO: Verifica si un slot está ocupado por una cita
     */
    private function isSlotOccupied(Carbon $slotStart, Carbon $slotEnd, $appointments, Carbon $date): bool
    {
        foreach ($appointments as $appointment) {
            $appointmentDate = Carbon::parse($appointment->appointment_date);

            if (!$appointmentDate->isSameDay($date)) {
                continue;
            }

            $appointmentStart = Carbon::parse($appointment->start_time);
            $appointmentEnd = Carbon::parse($appointment->end_time);

            if ($slotStart->lt($appointmentEnd) && $appointmentStart->lt($slotEnd)) {
                return true;
            }
        }

        return false;
    }

    /**
     * ✅ NUEVO: Valida si un horario específico está disponible
     */
    public function validateTimeSlot(
        int $employeeId,
        string $date,
        string $time,
        int $duration = 60,
        ?int $excludeAppointmentId = null
    ): array {
        $slotStart = Carbon::parse($date . ' ' . $time);
        $slotEnd = $slotStart->copy()->addMinutes($duration);

        // Verificar que el empleado tenga horario en ese momento
        $hasSchedule = $this->employeeHasScheduleAt($employeeId, $slotStart);

        if (!$hasSchedule) {
            return [
                'is_available' => false,
                'reason' => 'employee_not_scheduled',
                'message' => 'El empleado no tiene horario asignado en este momento.',
            ];
        }

        // Verificar que no haya citas solapadas
        $query = Appointment::where('employee_id', $employeeId)
            ->whereIn('status', ['programada', 'confirmada'])
            ->whereDate('appointment_date', $slotStart->format('Y-m-d'));

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        $appointments = $query->get();

        foreach ($appointments as $appointment) {
            $appointmentStart = Carbon::parse($appointment->start_time);
            $appointmentEnd = Carbon::parse($appointment->end_time);

            if ($slotStart->lt($appointmentEnd) && $appointmentStart->lt($slotEnd)) {
                return [
                    'is_available' => false,
                    'reason' => 'time_conflict',
                    'message' => 'Ya existe una cita en este horario.',
                    'conflicting_appointment' => [
                        'id' => $appointment->id,
                        'time' => $appointmentStart->format('H:i') . ' - ' . $appointmentEnd->format('H:i'),
                    ],
                ];
            }
        }

        return [
            'is_available' => true,
            'reason' => null,
            'message' => 'El horario está disponible.',
        ];
    }

    /**
     * ✅ NUEVO: Verifica si el empleado tiene horario asignado en un momento específico
     */
    private function employeeHasScheduleAt(int $employeeId, Carbon $datetime): bool
    {
        $schedules = StaffSchedule::where('staff_id', $employeeId)
            ->where('status', 'active')
            ->with('scheduleTemplate.scheduleDays')
            ->get();

        foreach ($schedules as $schedule) {
            if (!$schedule->appliesOnDate($datetime)) {
                continue;
            }

            $timeInfo = $schedule->getScheduleForDate($datetime);

            if (!$timeInfo['start_time'] || !$timeInfo['end_time']) {
                continue;
            }

            $workStart = Carbon::parse($datetime->format('Y-m-d') . ' ' . $timeInfo['start_time']);
            $workEnd = Carbon::parse($datetime->format('Y-m-d') . ' ' . $timeInfo['end_time']);

            if ($datetime->gte($workStart) && $datetime->lt($workEnd)) {
                return true;
            }
        }

        return false;
    }

    /**
     * ✅ NUEVO: Obtiene el próximo slot disponible
     */
    public function getNextAvailableSlot(
        int $employeeId,
        ?string $fromDate = null,
        int $duration = 60,
        int $maxDaysToSearch = 30
    ): ?array {
        $startDate = $fromDate ? Carbon::parse($fromDate) : Carbon::now();
        $endDate = $startDate->copy()->addDays($maxDaysToSearch);

        $availability = $this->getDoctorAvailability(
            $employeeId,
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
            $duration
        );

        foreach ($availability as $day) {
            foreach ($day['slots'] as $slot) {
                if ($slot['is_available']) {
                    return [
                        'date' => $day['date'],
                        'time' => $slot['start_time'],
                        'end_time' => $slot['end_time'],
                        'display' => $day['day_name'] . ', ' . $day['date'] . ' ' . $slot['display'],
                    ];
                }
            }
        }

        return null;
    }
}
