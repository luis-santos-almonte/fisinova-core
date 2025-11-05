<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InsuranceReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'insurance_id' => 'required|exists:insurances,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,excel',

            // Filtros opcionales (para futuras expansiones)
            'service_type' => 'nullable|in:consultation,therapy,admission',
            'patient_id' => 'nullable|exists:patients,id',
        ];
    }

    public function messages(): array
    {
        return [
            'insurance_id.required' => 'Debe seleccionar un seguro',
            'insurance_id.exists' => 'El seguro seleccionado no existe',
            'start_date.required' => 'La fecha de inicio es requerida',
            'end_date.required' => 'La fecha de fin es requerida',
            'end_date.after_or_equal' => 'La fecha fin debe ser mayor o igual a la fecha inicio',
            'format.required' => 'Debe seleccionar un formato',
            'format.in' => 'El formato debe ser PDF o Excel',
        ];
    }
}
