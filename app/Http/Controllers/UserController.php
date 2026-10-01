<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Obtener la lista de usuarios/organizaciones.
     */
    public function index()
    {
        return response()->json(User::latest()->get(), 200);
    }

    /**
     * Registrar un nuevo usuario o empresa.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ], [
            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.email'       => 'Ingresa un correo electrónico válido.',
            'email.unique'      => 'Este correo electrónico ya está registrado. Intenta con otro.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        // Mapeo flexible de nombres
        $userName = $request->input('admin_nombre') 
            ?? $request->input('name') 
            ?? 'Administrador';

        $user = User::create([
            'name'     => $userName,
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $request->input('role', 'admin_ti'),
        ]);

        return response()->json([
            'message' => 'Registrado correctamente.',
            'user'    => $user
        ], 201);
    }
}