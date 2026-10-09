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
        // validamos los datos basicos que envia el formulario de React
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:tecnico,empleado',
        ], [
            'name.required'     => 'El nombre del empleado es obligatorio.',
            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.unique'      => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        // creamos al empleado inyectando el id de la empresa del admin
        $user = User::create([
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'password'        => Hash::make($validated['password']),
            'role'            => $validated['role'], // 
            'organization_id' => $request->user()->organization_id,
        ]);

        return response()->json([
            'message' => 'Empleado registrado correctamente en tu empresa.',
            'user'    => $user
        ], 201);
    }

    /**
     * Eliminar un usuario por su ID.
     */
    public function destroy($id)
    {
        $user = User::find($id);
        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado correctamente'
        ], 200);
    }
}
