<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required_without:email|string',
            'email' => 'required_without:name|email',
            'password' => 'required|string',
        ]);

        $user = $request->name
            ? User::where('name', $request->name)->first()
            : User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => true,
                'message' => 'Credenciales inválidas',
                'data' => null
            ], 401);
        }

        $user->load(['roles' => function ($query) {
            $query->where('roles.active', true)
                ->where('role_user.active', true);
        }]);

        $userData = $user->only(['id', 'name', 'email']);
        $userData['roles'] = $user->roles->pluck('name')->toArray();
        $userData['rols'] = $user->roles->pluck('id')->toArray();

        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'error' => false,
            'message' => 'Inicio de sesión exitoso',
            'data' => $userData,
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'error' => false,
            'message' => 'Sesión cerrada exitosamente',
            'data' => null
        ], 200);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        $user->load(['roles' => function ($query) {
            $query->where('roles.active', true)
                ->where('role_user.active', true);
        }]);

        $userData = $user->only(['id', 'name', 'email']);
        $userData['roles'] = $user->roles->pluck('name')->toArray();
        $userData['rols'] = $user->roles->pluck('id')->toArray();

        return response()->json([
            'error' => false,
            'message' => 'Usuario autenticado',
            'data' => $userData
        ], 200);
    }
}
