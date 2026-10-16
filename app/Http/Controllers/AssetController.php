<?php
namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
        // Listar los equipos de la organización autenticada
    public function index(Request $request)
    {
        $assets = Asset::query()
            ->where('organization_id', $request->user()->organization_id)
            ->with('category')
            ->latest()
            ->get();

        return response()->json([
            'data' => $assets
        ]);
    }
    // guardar un nuevo equipo  en la ruta (POST /api/assets)
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            // Validacion de los campos del equipo
            'category_id' => 'required|exists:categories,id',
            'user_id' => 'nullable|exists:users,id',
            'code' => [
                'required',
                'string',
                Rule::unique('assets')->where(fn ($query) => $query->where('organization_id', $user->organization_id))
            ],
            'serial_number' => [
                'nullable',
                'string',
                Rule::unique('assets')->where(fn ($query) => $query->where('organization_id', $user->organization_id))
            ],
            'brand' => 'nullable|string',
            'model' => 'required|string',
            'location' => 'nullable|string',
            'status' => 'required|in:activo,mantenimiento,baja',
            'purchase_date' => 'nullable|date'
        ]);

        // Inyectamos la organizacion del usuario autenticado
        $validated['organization_id'] = $user->organization_id;

        // Creamos el equipo con los datos validados

        $asset = Asset::create($validated);

        return response()->json([
            'message' => 'Equipo registrado correctamente.',
            'asset' => $asset
        ], 201);
    }
}