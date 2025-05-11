<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;



class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {

            $request->validate([
                'name' => 'required_without:email|string',
                'email' => 'required_without:name|email',
                'password' => 'required|string',
            ]);

            $user = $request->name ? User::where('name', $request->name)->first() : User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Usuario o Contraseña Incorrectos'], 401);
            }

            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json(['user' => $user->only(['id', 'name', 'email']), 'token' => $token]);
        } catch (\Exception $e) {
            Log::error('Error en la validación de login', [
                'user_id' => optional($request->user())->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Error en la validación'], 422);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Sesión Cerrada']);
        } catch (\Exception $e) {
            Log::error('Error al cerrar sesión', [
                'user_id' => optional($request->user())->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Error al cerrar sesión'], 500);
        }
    }
}
