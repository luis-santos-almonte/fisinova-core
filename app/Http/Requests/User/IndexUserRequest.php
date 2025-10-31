<?php
// app/Http/Requests/User/IndexUserRequest.php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'sometimes|string|min:2|max:255',
            'active' => ['sometimes', Rule::in(['true', 'false', '1', '0'])],
            'paginate' => 'sometimes|integer|min:1|max:100',
        ];
    }
}

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
            'roles.required' => 'Debe asignar al menos un rol',
            'roles.min' => 'Debe asignar al menos un rol',
        ];
    }
}

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
            'roles' => 'sometimes|array|min:1',
            'roles.*' => 'exists:roles,id',
            'active' => 'sometimes|boolean',
        ];
    }
}

// app/Http/Requests/User/ChangePasswordRequest.php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
            'new_password_confirmation' => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'La contraseña actual es requerida',
            'new_password.required' => 'La nueva contraseña es requerida',
            'new_password.min' => 'La nueva contraseña debe tener al menos 6 caracteres',
            'new_password.confirmed' => 'Las contraseñas no coinciden',
        ];
    }
}