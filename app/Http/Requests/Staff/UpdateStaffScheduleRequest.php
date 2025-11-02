<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'staff_id' => 'sometimes|integer|exists:employees,id',  // ✅ CAMBIO: employees
            'schedule_day_id' => 'sometimes|integer|exists:schedule_days,id',
            'cubicle_id' => 'sometimes|integer|exists:cubicles,id',
            'assignment_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:assignment_date',
            'is_override' => 'sometimes|boolean',
            'original_staff_id' => 'sometimes|integer|exists:employees,id',  // ✅ CAMBIO: employees
            'status' => 'sometimes|string|in:active,cancelled,completed',
            'notes' => 'sometimes|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'staff_id.exists' => 'El personal seleccionado no existe',
            'schedule_day_id.exists' => 'El día de horario seleccionado no existe',
            'cubicle_id.exists' => 'El cubículo seleccionado no existe',
            'end_date.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio',
            'status.in' => 'El estado debe ser: active, cancelled o completed',
        ];
    }
}