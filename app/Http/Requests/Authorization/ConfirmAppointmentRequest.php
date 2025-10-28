<?php

namespace App\Http\Requests\Authorization;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'payment_type' => 'required|in:insurance,private',
            'notes' => 'nullable|string|max:2000',
        ];

        if ($this->input('payment_type') === 'insurance') {
            $rules['authorization_number'] = 'required|string|max:255';
            $rules['insurance_id'] = 'required|integer|exists:insurances,id';
            $rules['expiration_date'] = 'nullable|date|after:today';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'authorization_number.required' => 'El número de autorización es requerido para pagos con seguro',
            'insurance_id.required' => 'Debe seleccionar un seguro médico para pagos con seguro',
        ];
    }
}