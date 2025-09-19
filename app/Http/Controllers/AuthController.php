<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        $minutes = env('COOKIE_LIFETIME_MINUTES', 1440);

        $request->validate([
            'name' => 'required_without:email|string',
            'email' => 'required_without:name|email',
            'password' => 'required|string',
        ]);

        $user = $request->name
            ? User::where('name', $request->name)->first()
            : User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Credenciales inválidas', 'INVALID_CREDENTIALS', 401);
        }

        $token = $user->createToken('API Token')->plainTextToken;

        // Obtener roles del usuario
        $roles = $user->roles()
            ->where('roles.active', true)
            ->where('role_user.active', true)
            ->pluck('name')
            ->toArray();

        $cookie = cookie(
            'token',
            $token,
            $minutes,
            null,
            null,
            false,
            true,
            false,
            'Lax'
        );

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $roles, // ✅ Roles incluidos
                'rols' => array_map('intval', array_keys(array_flip($roles))), // Para compatibilidad con tu sistema actual
                'employee' => $user->employee ? [
                    'id' => $user->employee->id,
                    'firstname' => $user->employee->firstname,
                    'lastname' => $user->employee->lastname,
                    'position_id' => $user->employee->position_id
                ] : null
            ],
            'message' => 'Inicio de sesión exitoso'
        ])->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        $cookie = cookie(
            'token',
            null,
            -1,
            null,
            null,
            false,
            true,
            false,
            'Lax'
        );

        return $this->successResponse([
            'message' => 'Sesión cerrada exitosamente'
        ])->withCookie($cookie);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['roles' => function ($query) {
            $query->where('roles.active', true)
                ->where('role_user.active', true);
        }, 'employee']);

        $roles = $user->roles->pluck('name')->toArray();

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $roles,
            'rols' => array_map('intval', array_keys(array_flip($roles))),
            'employee' => $user->employee ? [
                'id' => $user->employee->id,
                'firstname' => $user->employee->firstname,
                'lastname' => $user->employee->lastname,
                'position_id' => $user->employee->position_id
            ] : null
        ]);
    }
}
