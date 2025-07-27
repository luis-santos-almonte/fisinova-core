<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;



class AuthController extends Controller
{
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
            return response()->json([
            'error' => true,
            'message' => 'Credenciales inválidas',
            'data' => null
        ], 401);
        }

        $token = $user->createToken('API Token')->plainTextToken;


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

        return response()->json([
            'error' => false,
            'message' => 'Inicio de sesión exitoso',
            'data' => $user->only(['id', 'name', 'email']),
        ], 200)->withCookie($cookie);
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

    return response()->json([
        'error' => false,
        'message' => 'Sesión cerrada exitosamente',
        'data' => null
    ], 200)->withCookie($cookie);
    }
}
