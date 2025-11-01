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
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'email' => 'nullable|email|max:255|unique:employees,email',  // ✅ CAMBIO: employees
            'phone' => 'nullable|string|max:20',
            'cellphone' => 'nullable|string|max:20',
            'dni' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'position_id' => 'required|integer|exists:positions,id',
            'active' => 'sometimes|boolean',
        ];
    }
}