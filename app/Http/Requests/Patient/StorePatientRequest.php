<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'dni' => 'nullable|string|max:20|unique:patients',
            'passport' => 'nullable|string|max:20',
            'sex' => 'nullable|string|max:10',
            'birthdate' => 'nullable|date|before:today',
            'email' => 'nullable|email|max:255|unique:patients',
            'phone' => 'nullable|string|max:20',
            'cellphone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:255',
            'insurance_code' => 'nullable|string|max:255',
            'insurance_id' => 'nullable|integer|exists:insurances,id',
        ];
    }
}