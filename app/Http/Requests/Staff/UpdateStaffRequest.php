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
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('staff')->ignore($staffId)
            ],
            'phone' => 'sometimes|string|max:20',
            'position_id' => 'sometimes|integer|exists:positions,id',
            'is_active' => 'sometimes|boolean',
        ];
    }
}