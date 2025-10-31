<?php
// app/Http/Requests/User/StoreUserRequest.php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'employee_id' => 'nullable|integer|exists:employees,id|unique:users,employee_id',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            'active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de usuario es requerido',
            'name.unique' => 'Este nombre de usuario ya está en uso',
            'email.required' => 'El email es requerido',
            'email.unique' => 'Este email ya está registrado',
            'employee_id.unique' => 'Este personal ya tiene un usuario asignado',
            'employee_id.exists' => 'El personal seleccionado no existe',
            'roles.required' => 'Debe asignar al menos un rol',
            'roles.min' => 'Debe asignar al menos un rol',
        ];
    }
}