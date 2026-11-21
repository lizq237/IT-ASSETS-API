<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
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
        $ticket = Ticket::find($id);
        if (! $ticket || $ticket->organization_id !== $request->user()->organization_id) {
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

        // Filtro de el usuario y su organización
        $tickets = Ticket::where('user_id', $user->id)
            ->where('organization_id', $user->organization_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($tickets, 200);
    }

    // Agregar comentario y control de actividad
    public function addComment(Request $request, $id)
    {
        $user = $request->user();
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $ticket = Ticket::find($id);

        // Validamos que exista y que pertenezca al usuario o a su organización
        if (! $ticket || ($ticket->user_id !== $user->id && $ticket->organization_id !== $user->organization_id)) {
            return response()->json(['message' => 'Ticket no encontrado o sin permisos.'], 404);
        }

        // Creamos el objeto del comentario
        $newComment = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role' => $user->role,
            'message' => $validated['message'],
            'created_at' => now()->toISOString(),
        ];

        // Obtenemos los comentarios actuales (si no hay, entonces eun arreglo vacío)
        $comments = $ticket->comments ?? [];

        $comments[] = $newComment;

        // Actualizamos el ticket en MongoDB
        $ticket->comments = $comments;
        $ticket->last_comment_by = $user->id;
        $ticket->save();

        // Hilo actualizado, delvolvemos
        return response()->json([
            'message' => 'Comentario agregado correctamente.',
            'ticket' => $ticket,
        ], 200);
    }
}
