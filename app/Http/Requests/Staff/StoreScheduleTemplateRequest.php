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
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'schedule_days' => 'required|array|min:1',
            'schedule_days.*.day_of_week' => 'nullable|integer|min:1|max:7',
            'schedule_days.*.start_time' => 'required|date_format:H:i',
            'schedule_days.*.end_time' => 'required|date_format:H:i',
            'schedule_days.*.is_recurring' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la plantilla es requerido',
            'schedule_days.required' => 'Debe proporcionar al menos un día de horario',
            'schedule_days.min' => 'Debe proporcionar al menos un día de horario',
            'schedule_days.*.start_time.required' => 'La hora de inicio es requerida',
            'schedule_days.*.start_time.date_format' => 'La hora de inicio debe tener formato HH:mm',
            'schedule_days.*.end_time.required' => 'La hora de fin es requerida',
            'schedule_days.*.end_time.date_format' => 'La hora de fin debe tener formato HH:mm',
            'schedule_days.*.end_time.after' => 'La hora de fin debe ser posterior a la hora de inicio',
            'schedule_days.*.day_of_week.min' => 'El día de la semana debe estar entre 1 y 7',
            'schedule_days.*.day_of_week.max' => 'El día de la semana debe estar entre 1 y 7',
        ];
    }
}