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
            'expiration_date' => 'nullable|date|after_or_equal:authorization_date',
            'authorization_type' => 'sometimes|in:initial,additional,extension',
            'notes' => 'nullable|string|max:2000',
            'services_authorized' => 'nullable|array',
            
            // ✅ NUEVO: Campos para manejo de sesiones de terapia
            'sessions_authorized' => 'nullable|integer|min:1|max:100',
            'diagnosis_codes' => 'nullable|array',
            'diagnosis_codes.*' => 'string|max:50',
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
            'sessions_authorized.min' => 'Debe autorizar al menos 1 sesión',
            'sessions_authorized.max' => 'No puede autorizar más de 100 sesiones',
        ];
    }
}