<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * funcion para que el admin registre usuarios
     */
    public function register(Request $request)
    {
        $user = auth('api')->user();

        if ($user && $user->role !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'in:admin,standard',
        ]);

        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'standard',
        ]);

        return response()->json(['message' => 'Usuario registrado con éxito'], 201);
    }

    /**
     * login
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = auth('api')->attempt($credentials)) {
                return response()->json(['message' => 'Credenciales incorrectas'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'No se pudo crear el token'], 500);
        }

        return $this->respondWithToken($token);
    }
    
    /**
     * funcion para refrescar el token
     */
    public function refresh()
    {
        try {
            $token = auth('api')->refresh(); 

            return response()->json([
                'auth_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => auth('api')->user()
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'No autorizado'], 401);
        }
    }

    /**
     * logout
     */
    public function logout(Request $request)
    {
        $token = $request->bearerToken();

        if ($token) {
            try {
                JWTAuth::setToken($token)->invalidate();
            } catch (JWTException $e) {
                return response()->json(['message' => 'Error cerrando sesión'], 500);
            }
        }

        return response()->json(['message' => 'Sesión cerrada']);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'auth_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user()
        ]);
    }
}
