<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'staff_id' => 'required|integer|exists:employees,id',  // ✅ CAMBIO: employees
            'schedule_day_id' => 'required|integer|exists:schedule_days,id',
            'cubicle_id' => 'nullable|integer|exists:cubicles,id',
            'assignment_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:assignment_date',
            'is_override' => 'sometimes|boolean',
            'original_staff_id' => 'nullable|integer|exists:employees,id',  // ✅ CAMBIO: employees
            'status' => 'sometimes|string|in:active,cancelled,completed',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'staff_id.required' => 'El personal es requerido',
            'schedule_day_id.required' => 'El día de horario es requerido',
            'end_date.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio',
        ];
    }
}