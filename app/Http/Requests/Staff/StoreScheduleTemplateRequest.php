<?php

namespace App\Http\Requests\Staff;
// namespace App\Http\Requests\StaffSchedule;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'staff_id' => 'required|integer|exists:staff,id',
            'schedule_day_id' => 'required|integer|exists:schedule_days,id',
            'cubicle_id' => 'nullable|integer|exists:cubicles,id',
            'assignment_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:assignment_date',
            'is_override' => 'sometimes|boolean',
            'original_staff_id' => 'nullable|integer|exists:staff,id',
            'status' => 'sometimes|string|in:active,cancelled,completed',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'staff_id.required' => 'El personal es requeri2',
            'staff_id.exists' => 'El personal seleccionado no existe',
            'schedule_day_id.required' => 'El día de horario es requerido',
            'schedule_day_id.exists' => 'El día de horario seleccionado no existe',
            'cubicle_id.exists' => 'El cubículo seleccionado no existe',
            'end_date.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio',
            'status.in' => 'El estado debe ser: active, cancelled o completed',
        ];
    }
}