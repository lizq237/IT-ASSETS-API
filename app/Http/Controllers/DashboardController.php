<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Información del dashboard del tenant autenticado.
     *
     * - Los contadores de usuarios son REALES y ya están scopeados por
     *   TenantScope (un admin_ti solo ve los usuarios de su organización;
     *   un super_admin ve todos).
     * - Los contadores de equipos y tickets devuelven 0 en Sprint 1 porque
     *   sus tablas/colecciones se crean en Sprint 2. El contrato de este
     *   endpoint NO cambia: cuando existan las tablas, solo se reemplaza
     *   el cuerpo de este método.
     * 
     * SUELTE :)
     */
    public function resumen(): JsonResponse
    {
        return response()->json([
            'data' => [
                'equipos' => [
                    'total'         => 0,
                    'asignados'     => 0,
                    'disponibles'   => 0,
                    'mantenimiento' => 0,
                ],
                'usuarios' => [
                    'total'       => User::count(),
                    'admins'      => User::where('role', 'admin_ti')->count(),
                    'responsables' => User::where('role', 'empleado')->count(),
                    'tecnicos'    => User::where('role', 'tecnico')->count(),
                ],
                'tickets' => [
                    'abiertos'   => 0,
                    'en_proceso' => 0,
                    'resueltos'  => 0,
                ],
            ],
        ]);
    }
}