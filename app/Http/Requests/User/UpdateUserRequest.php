<?php
// app/Http/Requests/User/UpdateUserRequest.php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],
            'employee_id' => [
                'nullable',
                'integer',
                'exists:employees,id',
                Rule::unique('users')->ignore($userId)
            ],
            'roles' => 'sometimes|array|min:1',
            'roles.*' => 'exists:roles,id',
            'active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Este nombre de usuario ya está en uso',
            'email.unique' => 'Este email ya está registrado',
            'employee_id.unique' => 'Este personal ya tiene un usuario asignado',
            'employee_id.exists' => 'El personal seleccionado no existe',
            'roles.min' => 'Debe asignar al menos un rol',
        ];
    }
}