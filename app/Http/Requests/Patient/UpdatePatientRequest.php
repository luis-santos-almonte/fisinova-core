<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'firstname' => 'sometimes|string|max:255',
            'lastname' => 'sometimes|string|max:255',
            'dni' => 'sometimes|string|max:20',
            'passport' => 'sometimes|string|max:20',
            'sex' => 'sometimes|string|max:10',
            'birthdate' => 'sometimes|date',
            'email' => 'sometimes|email|max:255',
            'phone' => 'sometimes|string|max:20',
            'cellphone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:255',
        ];
    }
}
