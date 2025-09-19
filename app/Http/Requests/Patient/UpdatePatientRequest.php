<?php

namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $patientId = $this->route('patient')->id;
        
        return [
            'firstname' => 'sometimes|string|max:255',
            'lastname' => 'sometimes|string|max:255',
            'dni' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('patients')->ignore($patientId)
            ],
            'passport' => 'sometimes|string|max:20',
            'sex' => 'sometimes|string|max:10',
            'birthdate' => 'sometimes|date|before:today',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('patients')->ignore($patientId)
            ],
            'phone' => 'sometimes|string|max:20',
            'cellphone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:255',
            'insurance_code' => 'sometimes|string|max:255',
            'insurance_id' => 'sometimes|integer|exists:insurances,id',
        ];
    }
}