<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Ticket;

class TicketController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->organization_id) {
            return response()->json([
                'message' => 'Tu cuenta no tiene una organización asignada.'
                ], 403);
        }
        
        $validated = $request->validate([
        'asset_id' => 'required|integer',
        'title' => 'required|string|max:150',
        'description' => 'required|string|max:2000',
        'priority' => 'required|in:baja,media,alta',
        ]);

    $asset = Asset::where('id', $validated['asset_id'])
    ->where('organization_id', $user->organization_id)
    ->where('assigned_user_id', $user->id)
    ->first();

            if (!$asset) {
            return response()->json([
                'message' => 'El equipo seleccionado no está disponible para reportar.',
                'errors' => [
                    'asset_id' => [
                        'Selecciona un equipo asignado a tu cuenta.'
                    ]
                ]
            ], 422);
        }

        $ticket = Ticket::create([
            'organization_id' => $user->organization_id,
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => 'abierto',
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
                'has_evidence' => false,
            ],
        ], 201);
    }
}