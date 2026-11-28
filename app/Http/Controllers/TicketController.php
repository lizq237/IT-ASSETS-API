<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    // HU Kanban / #24: alcance común de los tickets según el rol.
    private function visibleTickets($user)
    {
        $query = Ticket::where('organization_id', $user->organization_id);
        if ($user->role === 'admin_ti') {
            return $query;
        }
        if ($user->role === 'tecnico') {
            return $query->where(function ($query) use ($user) {
                $query->where('assigned_technician_id', $user->id)
                    ->orWhere(function ($query) {
                        $query->whereNull('assigned_technician_id')->where('status', 'abierto');
                    });
            });
        }
        return $query->where('user_id', $user->id);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user->organization_id && in_array($user->role, ['admin_ti', 'tecnico'], true), 403);

        $tickets = $this->visibleTickets($user)->orderBy('created_at', 'desc')->get();
        // Consultas agrupadas a la base relacional, sin consultas por cada tarjeta.
        $assets = Asset::withTrashed()->where('organization_id', $user->organization_id)
            ->whereIn('id', $tickets->pluck('asset_id')->filter()->unique()->values()->all())
            ->with('location')->get()->keyBy('id');
        $users = User::where('organization_id', $user->organization_id)
            ->whereIn('id', $tickets->pluck('user_id')->filter()->unique()->values()->all())
            ->get(['id', 'name'])->keyBy('id');

        return response()->json($tickets->map(function ($ticket) use ($assets, $users) {
            $asset = $assets->get($ticket->asset_id);
            $reporter = $users->get($ticket->user_id);
            return array_merge($ticket->toArray(), [
                'id' => (string) $ticket->id,
                'asset' => $asset ? [
                    'id' => $asset->id, 'code' => $asset->code, 'model' => $asset->model,
                    'location' => $asset->location?->name,
                ] : null,
                'reporter' => $reporter ? ['id' => $reporter->id, 'name' => $reporter->name] : null,
                'has_evidence' => (bool) $ticket->image_path,
                'evidence_url' => $ticket->image_path ? '/api/tickets/'.$ticket->id.'/evidence' : null,
            ]);
        })->values());
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (! $user->organization_id) {
            return response()->json([
                'message' => 'Tu cuenta no tiene una organización asignada.',
            ], 403);
        }

        // Validación de la imagen (opcional, max 5MB, solo JPEG/PNG)
        $validated = $request->validate([
            'asset_id' => 'required|integer',
            'title' => 'required|string|max:150',
            'description' => 'required|string|max:2000',
            'priority' => 'required|in:baja,media,alta',
            'image' => 'nullable|image|mimes:jpeg,png|max:5120', // 5120 KB = 5 MB
        ]);

        $asset = Asset::where('id', $validated['asset_id'])
            ->where('organization_id', $user->organization_id)
            ->where('assigned_user_id', $user->id)
            ->first();

        if (! $asset) {
            return response()->json([
                'message' => 'El equipo seleccionado no está disponible para reportar.',
                'errors' => [
                    'asset_id' => ['Selecciona un equipo asignado a tu cuenta.'],
                ],
            ], 422);
        }

        // Guardala imagen en el almacenamiento privado
        $imagePath = null;
        if ($request->hasFile('image')) {
            // Se guardada en storage/app/private/tickets/
            $imagePath = $request->file('image')->store('tickets', 'local');
        }

        $ticket = Ticket::create([
            'organization_id' => $user->organization_id,
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => 'abierto',
            'image_path' => $imagePath, // Guarda la ruta en Mongo
        ]);

        return response()->json([
            'message' => 'Ticket registrado correctamente.',
            'ticket' => [
                'id' => (string) $ticket->id,
                'asset_id' => $ticket->asset_id,
                'title' => $ticket->title,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'created_at' => $ticket->created_at?->toISOString(),
                'has_evidence' => $imagePath !== null,
            ],
        ], 201);
    }

    // Ruta protegida para ver la imagen
    public function getEvidence(Request $request, $id)
    {
        // Se busca el ticket por su ID de MongoDB
        abort_unless($request->user()->organization_id, 403);
        $ticket = $this->visibleTickets($request->user())->find($id);
        if (! $ticket) {
            return response()->json(['message' => 'Ticket no encontrado o sin permisos.'], 404);
        }

        // Verificamos que tenga una imagen guardada
        if (! $ticket->image_path || ! Storage::disk('local')->exists($ticket->image_path)) {
            return response()->json(['message' => 'Este ticket no tiene una imagen adjunta.'], 404);
        }

        return Storage::disk('local')->download($ticket->image_path);
    }

    // COnsuktando tickets del empleado autenticado
    public function employeeTickets(Request $request)
    {
        $user = $request->user();

        abort_unless($user->organization_id, 403);

        // Filtro de el usuario y su organización
        $tickets = Ticket::where('user_id', $user->id)
            ->where('organization_id', $user->organization_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($tickets, 200);
    }

    // #30: mantiene comentarios existentes y agrega notas de resolución.
    public function addComment(Request $request, $id)
    {
        $user = $request->user();
        abort_unless($user->organization_id, 403);
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'is_resolution' => 'sometimes|boolean',
            'resolve' => 'sometimes|boolean',
        ]);
        $isResolution = (bool) ($validated['is_resolution'] ?? false);
        $resolve = (bool) ($validated['resolve'] ?? false);
        abort_if(($isResolution || $resolve) && ! in_array($user->role, ['admin_ti', 'tecnico'], true), 403);
        if ($resolve && ! $isResolution) {
            return response()->json(['message' => 'Marca el comentario como resolución.'], 422);
        }

        $ticket = $this->visibleTickets($user)->find($id);
        abort_unless($ticket, 404);
        if ($isResolution && $ticket->status === 'cerrado') {
            return response()->json(['message' => 'El ticket está cerrado.'], 422);
        }

        $now = now();
        $comment = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role' => $user->role,
            'message' => $validated['message'],
            'is_resolution' => $isResolution,
            'created_at' => $now->toISOString(),
        ];
        $fields = ['last_comment_by' => $user->id, 'updated_at' => $now];
        if ($isResolution) {
            $fields['resolution_notes'] = $validated['message'];
            $fields['resolved_at'] = $now;
        }
        if ($resolve) {
            $fields['status'] = 'resuelto';
        }
        // Al resolver un ticket disponible, queda asociado al técnico que lo atendió.
        if ($isResolution && $user->role === 'tecnico' && ! $ticket->assigned_technician_id) {
            $fields['assigned_technician_id'] = $user->id;
        }
        $query = $this->visibleTickets($user)->whereKey($id);
        if ($isResolution) {
            $query->where('status', '!=', 'cerrado');
        }
        // Una sola operación MongoDB evita perder comentarios concurrentes.
        $updated = $query->toBase()->update(['$push' => ['comments' => $comment], '$set' => $fields]);
        abort_unless($updated, 409, 'El ticket cambió. Actualiza la consulta.');

        return response()->json([
            'message' => 'Comentario agregado correctamente.',
            'ticket' => Ticket::where('organization_id', $user->organization_id)->find($id),
        ]);
    }
}
