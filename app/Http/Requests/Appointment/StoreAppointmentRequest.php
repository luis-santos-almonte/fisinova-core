<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|integer|exists:employees,id',
            'patient_id' => 'nullable|integer|exists:patients,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'sometimes|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'dni' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'passport' => 'nullable|string|max:20',
            'insurance_code' => 'nullable|string|max:255',
            'insurance_id' => 'nullable|integer|exists:insurances,id',
            'guest_firstname' => 'nullable|string|max:100',
            'guest_lastname' => 'nullable|string|max:100',
            'active' => 'sometimes|boolean',
        ];
    }
}
