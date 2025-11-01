<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $staffId = $this->route('staff')->id;

        return [
            'firstname' => 'sometimes|string|max:100',
            'lastname' => 'sometimes|string|max:100',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('employees')->ignore($staffId)  // ✅ CAMBIO: employees
            ],
            'phone' => 'sometimes|string|max:20',
            'cellphone' => 'sometimes|string|max:20',
            'dni' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500',
            'position_id' => 'sometimes|integer|exists:positions,id',
            'active' => 'sometimes|boolean',  // ✅ CAMBIO: active
        ];
    }
}