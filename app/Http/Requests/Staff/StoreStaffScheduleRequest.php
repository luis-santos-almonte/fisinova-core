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
            'staff_id' => 'required|integer|exists:employees,id',
            'schedule_template_id' => 'required|integer|exists:schedule_templates,id',
            
            // ✅ NUEVO: Días seleccionados (opcional, array de 1-7)
            'selected_days' => 'nullable|array',
            'selected_days.*' => 'integer|min:1|max:7',
            
            'cubicle_id' => 'nullable|integer|exists:cubicles,id',
            
            // ✅ RENOMBRADO: Fechas de vigencia
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            
            // ✅ NUEVO: Asignación específica (puntual)
            'specific_date' => 'nullable|date',
            'specific_start_time' => 'required_with:specific_date|date_format:H:i',
            'specific_end_time' => 'required_with:specific_date|date_format:H:i|after:specific_start_time',
            
            'is_override' => 'sometimes|boolean',
            'original_staff_id' => 'nullable|integer|exists:employees,id',
            'status' => 'sometimes|string|in:active,cancelled,completed',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'staff_id.required' => 'El personal es requerido',
            'schedule_template_id.required' => 'Debe seleccionar un horario',
            'selected_days.*.min' => 'Los días deben estar entre 1 (Lunes) y 7 (Domingo)',
            'selected_days.*.max' => 'Los días deben estar entre 1 (Lunes) y 7 (Domingo)',
            'end_date.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio',
            'specific_start_time.required_with' => 'Debe especificar hora de inicio para fecha específica',
            'specific_end_time.required_with' => 'Debe especificar hora de fin para fecha específica',
            'specific_end_time.after' => 'La hora de fin debe ser posterior a la hora de inicio',
        ];
    }

    /**
     * ✅ Validación personalizada
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // No puede tener ambos: recurrente Y específico
            if ($this->specific_date && ($this->start_date || $this->end_date)) {
                $validator->errors()->add(
                    'specific_date',
                    'No puede ser asignación específica y recurrente al mismo tiempo'
                );
            }

            // Si es específico, no necesita selected_days
            if ($this->specific_date && $this->selected_days) {
                $validator->errors()->add(
                    'selected_days',
                    'Las asignaciones específicas no requieren días seleccionados'
                );
            }
        });
    }
}