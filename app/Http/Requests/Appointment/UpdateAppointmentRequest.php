<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'sometimes|integer|exists:employees,id',
            'patient_id' => 'sometimes|integer|nullable|exists:patients,id',
            'appointment_date' => 'sometimes|date',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'status' => 'sometimes|string|max:50',
            'notes' => 'sometimes|string|max:1000',
            'dni' => 'sometimes|string|max:20',
            'phone' => 'sometimes|string|max:20',
            'passport' => 'sometimes|string|max:20',
            'insurance_code' => 'sometimes|string|max:255',
            'insurance_id' => 'sometimes|integer|exists:insurances,id',
        ];
    }
}
