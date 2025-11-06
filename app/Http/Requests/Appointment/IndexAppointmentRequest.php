<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'active' => ['sometimes', Rule::in(['true', 'false', '1', '0'])],
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after_or_equal:start_date',
            'employee_id' => 'sometimes|integer|exists:employees,id',
            'patient_id' => 'sometimes|integer|exists:patients,id',
            'status' => 'sometimes|string|max:50',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'paginate' => 'sometimes|integer|min:1|max:100',
        ];
    }
}
