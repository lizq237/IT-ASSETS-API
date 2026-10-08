<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    /**
     * Registrar una nueva empresa (tenant) y a su administrador principal.
     */
    public function store(Request $request)
    {
        // 1. Validar los datos exactos que envía React
        $validated = $request->validate([
            'empresa_nombre' => 'required|string|max:255',
            'rfc'            => 'nullable|string|max:13',
            'admin_nombre'   => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:8',
        ]);

        // 2. Usar una transacción para asegurar que ambos registros se guarden o ninguno
        DB::beginTransaction();

        try {
            //Crear la organización primero
            $organization = Organization::create([
                'company_name' => $validated['empresa_nombre'],
                'RFC'  => $validated['rfc'] ?? null,
            ]);

            // crear al administrador y amarrarlo a la organizacion recien creada
            $user = User::create([
                'name'            => $validated['admin_nombre'],
                'email'           => $validated['email'],
                'password'        => Hash::make($validated['password']),
                'role'            => 'admin_ti', 
                'organization_id' => $organization->id, 
            ]);

            DB::commit(); // Confirmar los cambios en la base de datos

            return response()->json([
                'message' => 'Empresa y usuario registrados correctamente.',
                'organization' => $organization,
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack(); // Si algo falla, deshacemos todo para no dejar datos huerfanos
            return response()->json([
                'message' => 'Hubo un error al registrar la empresa.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}