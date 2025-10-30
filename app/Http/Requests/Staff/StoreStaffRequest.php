<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255|unique:staff,email',
            'phone' => 'nullable|string|max:20',
            'position_id' => 'required|integer|exists:positions,id',
            'is_active' => 'sometimes|boolean',
        ];
    }
}