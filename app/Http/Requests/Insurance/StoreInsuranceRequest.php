<?php
// app/Http/Requests/Insurance/StoreInsuranceRequest.php

namespace App\Http\Requests\Insurance;

use Illuminate\Foundation\Http\FormRequest;

class StoreInsuranceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'provider_code' => 'required|string|max:255|unique:insurances,provider_code',
            'active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del seguro es requerido',
            'name.max' => 'El nombre no puede exceder 255 caracteres',
            'provider_code.required' => 'El código del proveedor es requerido',
            'provider_code.unique' => 'Este código de proveedor ya existe',
            'provider_code.max' => 'El código no puede exceder 255 caracteres',
            'active.boolean' => 'El estado debe ser verdadero o falso',
        ];
    }
}