<?php

namespace App\Services;

use App\Models\Appointment;

class AppointmentService
{
    public function getAllAppointments(array $filters = [])
    {
        $query = Appointment::query();

        // Apply filters
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
}