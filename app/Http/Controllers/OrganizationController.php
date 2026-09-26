<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class OrganizationController extends Controller
{
    public function index(): JsonResponse
    {
        $organizations = Organization::withCount('users')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $organizations,
        ]);
    }

    /**
     * Elimina una organización y en cascada sus usuarios asociados
     * (cascadeOnDelete en la FK de users.organization_id).
     *
     * Solo super_admin. Cuando en Sprint 2 existan equipos, aulas y tickets,
     * también se eliminarán en cascada (sus FKs ya deben declarar cascade).
     */
    public function destroy(Organization $organization): JsonResponse
    {
        $name = $organization->company_name;
        $organization->delete();

        return response()->json([
            'message' => "Organización \"{$name}\" eliminada correctamente.",
        ]);
    }
}