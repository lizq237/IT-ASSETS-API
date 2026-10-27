<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $locations = Location::where('organization_id', $request->user()->organization_id)
            ->latest()
            ->get();
            
        return response()->json(['data' => $locations]);
    }

    public function show(Request $request, $id)
    {
        $location = Location::where('organization_id', $request->user()->organization_id)->findOrFail($id);
        return response()->json(['data' => $location]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('locations')->where(fn ($query) => $query->where('organization_id', $request->user()->organization_id))
            ]
        ]);

        $validated['organization_id'] = $request->user()->organization_id;
        $location = Location::create($validated);

        return response()->json(['message' => 'Ubicación registrada correctamente.', 'data' => $location], 201);
    }

    public function update(Request $request, $id)
    {
        $location = Location::where('organization_id', $request->user()->organization_id)->findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('locations')->ignore($location->id)->where(fn ($query) => $query->where('organization_id', $request->user()->organization_id))
            ]
        ]);

        $location->update($validated);

        return response()->json(['message' => 'Ubicación actualizada correctamente.', 'data' => $location]);
    }

    public function destroy(Request $request, $id)
    {
        $location = Location::where('organization_id', $request->user()->organization_id)->findOrFail($id);
        $location->delete();

        return response()->json(['message' => 'Ubicación eliminada correctamente.']);
    }
}