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
        ];
    }
}
