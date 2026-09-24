<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    public function registerTenant(Request $request)
    {
        // 1. Validar que React nos envíe toda la información necesaria
        $request->validate([
            'empresa_nombre' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:13',
            'admin_nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // 2. Iniciamos la transacción (si algo falla, se cancela todo)
        DB::beginTransaction();

        try {
            // 3. Crear primero la empresa (Organizacion)
            $organization = Organization::create([
                'company_name' => $request->empresa_nombre,
                'RFC' => $request->rfc,
            ]);

            // 4. Crear al usuario y asignarlo a la empresa recién creada
            $user = User::create([
                'name' => $request->admin_nombre,
                'email' => $request->email,
                'password' => Hash::make($request->password), // Encriptamos la contraseña
                'organization_id' => $organization->id,
                'role' => 'Administrador de TI', // Le damos el rol máximo automáticamente
            ]);

            // Si todo salió bien, guardamos los cambios definitivamente
            DB::commit();

            return response()->json([
                'message' => 'Empresa y administrador registrados con éxito.',
                'organization' => $organization,
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            // Si hubo un error, deshacemos los cambios para no dejar datos a medias
            DB::rollBack();
            return response()->json([
                'message' => 'Hubo un error al registrar la empresa.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}