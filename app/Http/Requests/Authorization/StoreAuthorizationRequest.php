<?php

namespace App\Http\Requests\Authorization;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuthorizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_id' => 'required|integer|exists:appointments,id',
            'patient_id' => 'required|integer|exists:patients,id',
            'insurance_id' => 'nullable|integer|exists:insurances,id',
            'authorization_number' => 'required|string|max:255|unique:authorizations,authorization_number',
            'authorization_date' => 'required|date',
            'authorization_type' => 'sometimes|in:ambulatoria,hospitalizacion',
            
            // ✅ NUEVO: Montos
            'insurance_amount' => 'required|numeric|min:0',
            'patient_amount' => 'required|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            
            'notes' => 'nullable|string|max:2000',
            'services_authorized' => 'nullable|array',
            'diagnosis_codes' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'appointment_id.required' => 'La cita es requerida',
            'patient_id.required' => 'El paciente es requerido',
            'authorization_number.required' => 'El número de autorización es requerido',
            'authorization_number.unique' => 'Este número de autorización ya existe',
            'authorization_date.required' => 'La fecha de autorización es requerida',
            'insurance_amount.required' => 'El monto del seguro es requerido',
            'patient_amount.required' => 'El copago del paciente es requerido',
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