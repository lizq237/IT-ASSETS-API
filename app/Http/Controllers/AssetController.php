<?php
namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetController extends Controller{
    public function index(Request $request){
        $assets = Asset::query()
            ->where('organization_id', $request->user()->organization_id)
            ->with(['category', 'assignedUser']) // Incluimos al usuario asignado
            ->latest()
            ->get();

        return response()->json([
            'data' => $assets
        ]);
    }

    public function store(Request $request){
        $user = $request->user();

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'assigned_user_id' => 'nullable|exists:users,id',
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

        $validated['organization_id'] = $user->organization_id;

        $asset = Asset::create($validated);

        return response()->json([
            'message' => 'Equipo registrado correctamente.',
            'asset' => $asset
        ], 201);
    }

    // Método para la segunda tarea del sprint 3 (vincular equipos a un empleaso)
    public function assign(Request $request, $id){
        $asset = Asset::findOrFail($id);

        // Verificar que el equipo esté disponible (activo y sin asignar)
        if($asset->status !== 'activo'){
            return response()->json(['message' => 'El equipo no está activo.'], 422);
        }
        
        // if($asset->assigned_user_id !== null){
        //     return response()->json(['message' => 'El equipo ya está asignado a otro usuario.'], 422);
        // }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        // Verificar que el usuario pertenezca a la misma organización
        $targetUser = User::where('id', $validated['user_id'])
            ->where('organization_id', $request->user()->organization_id)
            ->first();

        if(!$targetUser){
            return response()->json(['message' => 'El usuario no pertenece a tu organización.'], 422);
        }

        $asset->assigned_user_id = $targetUser->id;
        $asset->save();

        return response()->json([
            'message' => 'Equipo asignado correctamente.',
            'asset' => $asset->load('assignedUser')
        ]);
    }

    //Metodo para cambiar estado de un equipo
    public function updateStatus(Request $request, $id){
        //Buscar el equipo (el TenantScope ya garantiza que sea de su organización)
        $asset = Asset::findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|in:activo,mantenimiento,baja'
        ]);

        $user = $request->user();

        if($user->role === 'tecnico' && $asset->assigned_user_id !== $user->id){
            return response()->json([
                'message' => 'No tienes permisos para cambiar el estado de un equipo que no tienes asignado.'
            ], 403);
        }

        $asset->status = $validated['status'];
        $asset->save();

        return response()->json([
            'message' => 'Estado actualizado correctamente.',
            'asset' => $asset
        ], 200);
    }

    //Eliminar un equipo (Solo admin_ti, obvaimente)
    public function destroy($id){
        $asset = Asset::findOrFail($id);
        $asset->delete(); //Usamos SoftDeletes (no borra de la BD, solo lo oculta)
        return response()->json([
            'message' => 'Equipo eliminado correctamente.'
        ], 200);
    }
}