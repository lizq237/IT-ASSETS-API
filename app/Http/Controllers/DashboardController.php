<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller{
    public function resumen(): JsonResponse
    {
        return response()->json([
            'data' => [
                'equipos' => [
                    'total' => \App\Models\Asset::count(),
                    'disponibles' => \App\Models\Asset::where('status', 'activo')->count(),
                    'mantenimiento' => \App\Models\Asset::where('status', 'mantenimiento')->count(),
                    'baja' => \App\Models\Asset::where('status', 'baja')->count(),
                ],
                'usuarios' => [
                    'total' => \App\Models\User::count(),
                    'responsables' => \App\Models\User::where('role', 'responsable')->count(),
                    'tecnicos' => \App\Models\User::where('role', 'tecnico')->count(),
                ],
            ],
        ]);
    }
}