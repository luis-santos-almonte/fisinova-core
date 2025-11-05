<?php
// app/Http/Requests/Insurance/UpdateInsuranceRequest.php

namespace App\Http\Requests\Insurance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInsuranceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $insuranceId = $this->route('insurance')->id;

        return [
            'name' => 'sometimes|string|max:255',
            'provider_code' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('insurances', 'provider_code')->ignore($insuranceId)
            ],
            'active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'El nombre no puede exceder 255 caracteres',
            'provider_code.unique' => 'Este código de proveedor ya existe',
            'provider_code.max' => 'El código no puede exceder 255 caracteres',
            'active.boolean' => 'El estado debe ser verdadero o falso',
        ];
    }
}