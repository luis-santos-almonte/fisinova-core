<?php

namespace App\Http\Requests\Authorization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAuthorizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $authorizationId = $this->route('authorization')->id;

        return [
            'appointment_id' => 'sometimes|integer|exists:appointments,id',
            'patient_id' => 'sometimes|integer|exists:patients,id',
            'insurance_id' => 'sometimes|integer|exists:insurances,id',
            'authorization_number' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('authorizations')->ignore($authorizationId)
            ],
            'authorization_date' => 'sometimes|date',
            'expiration_date' => 'sometimes|date|after_or_equal:authorization_date',
            'authorization_type' => 'sometimes|in:initial,additional,extension',
            'notes' => 'sometimes|string|max:2000',
            'services_authorized' => 'sometimes|array',
        ];
    }
}
