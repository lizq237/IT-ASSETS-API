<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validar que el frontend mande los datos obligatorios
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Intentar iniciar sesión verificando en la base de datos
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Correo o contraseña incorrectos'
            ], 401); // 401 significa "No autorizado"
        }

        // 3. Si las credenciales son correctas, obtenemos los datos del usuario
        $user = Auth::user();

        // 4. Le generamos un token de seguridad (para que React lo guarde)
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Respondemos con éxito, mandando el token y la información (aquí va el 'role')
        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'access_token' => $token,
            'user' => $user
        ], 200);
    }
}