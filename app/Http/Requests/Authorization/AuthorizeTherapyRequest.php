<?php

namespace App\Http\Requests\Authorization;

use Illuminate\Foundation\Http\FormRequest;

class AuthorizeTherapyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'authorization_number' => 'required|string|max:255',
            'authorization_date' => 'nullable|date',
            'insurance_id' => 'required|integer|exists:insurances,id',
            'sessions_authorized' => 'required|integer|min:1|max:100',
            
            // ✅ NUEVO: Montos obligatorios
            'insurance_amount' => 'required|numeric|min:0',
            'patient_amount' => 'required|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            
            'notes' => 'nullable|string|max:2000',
            'therapist_id' => 'nullable|integer|exists:employees,id',
            
            'sessions' => 'required|array|min:1',
            'sessions.*.date' => 'required|date|after_or_equal:today',
            'sessions.*.startTime' => 'required|date_format:H:i:s',
            'sessions.*.endTime' => 'required|date_format:H:i:s|after:sessions.*.startTime',
        ];
    }

    public function messages(): array
    {
        return [
            'authorization_number.required' => 'El número de autorización es requerido',
            'insurance_id.required' => 'Debe seleccionar un seguro',
            'sessions_authorized.required' => 'Debe especificar el número de sesiones autorizadas',
            
            // ✅ NUEVO: Mensajes de montos
            'insurance_amount.required' => 'El monto del seguro es requerido',
            'patient_amount.required' => 'El copago del paciente es requerido',
            
            'therapist_id.exists' => 'El terapista seleccionado no existe',
            'sessions.required' => 'Debe proporcionar las sesiones programadas',
            'sessions.*.date.required' => 'Cada sesión debe tener una fecha',
            'sessions.*.date.after_or_equal' => 'Las fechas de las sesiones no pueden ser en el pasado',
        ];
    }

    protected function prepareForValidation()
    {
        // Calcular total automáticamente si no viene
        if (!$this->has('total_amount')) {
            $this->merge([
                'total_amount' => ($this->insurance_amount ?? 0) + ($this->patient_amount ?? 0)
            ]);
        }
    }
}