<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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
        if (!$request->user()->organization_id) {
            return response()->json([
                'message' => 'Tu cuenta no tiene una organización asignada.'
            ], 403);
        }
        // validamos los datos basicos que envia el formulario de React
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|in:tecnico,responsable',
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
            'password' => $request->password,
            'role'            => $validated['role'],
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
    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'No puedes eliminar tu propia cuenta.'
                ], 403);
        }
        
        $user->delete();
        return response()->json([
            'message' => 'Usuario eliminado correctamente.'
            ], 200);
    }
}
