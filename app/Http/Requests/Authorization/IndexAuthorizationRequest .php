<?php

namespace App\Http\Requests\Authorization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAuthorizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'active' => ['sometimes', Rule::in(['true', 'false', '1', '0'])],
            'appointment_id' => 'sometimes|integer|exists:appointments,id',
            'patient_id' => 'sometimes|integer|exists:patients,id',
            'insurance_id' => 'sometimes|integer|exists:insurances,id',
            'authorization_number' => 'sometimes|string|max:255',
            'from_date' => 'sometimes|date',
            'to_date' => 'sometimes|date|after_or_equal:from_date',
            'paginate' => 'sometimes|integer|min:1|max:100',
        ];
    }
}
