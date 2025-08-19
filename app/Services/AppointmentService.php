<?php

namespace App\Services;

use App\Models\Appoinntment;
use App\Models\Appointment;
use App\Validators\Appointment\AppointmentFilterValidator;

class AppointmentService
{
    public function getAllAppointments(array $filters = [])
    {
        $filters = AppointmentFilterValidator::validate($filters);

        $pagination = $filters['paginate'] ?? 10;
        $query = Appointment::query();

        if (isset($filters['active'])) {
            $query->active($filters['active'] === 'true' || $filters['active'] === 1);
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

        if (!empty($filters['insurance_id'])) {
            $query->where('insurance_id', $filters['insurance_id']);
        }

        if (!empty($filters['dni'])) {
            $query->where('dni', $filters['dni']);
        }

        if (!empty($filters['phone'])) {
            $query->where('phone', $filters['phone']);
        }

        if (!empty($filters['passport'])) {
            $query->where('passport', $filters['passport']);
        }

        return $query
            ->with(['employee', 'insurance'])
            ->simplePaginate($pagination);
    }

    public function getAppointmentById($id)
    {
        return Appointment::with(['employee', 'insurance'])->findOrFail($id);
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
