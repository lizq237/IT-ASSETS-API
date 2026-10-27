<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $brands = Brand::where('organization_id', $request->user()->organization_id)
            ->latest()
            ->get();
            
        return response()->json(['data' => $brands]);
    }

    public function show(Request $request, $id)
    {
        $brand = Brand::where('organization_id', $request->user()->organization_id)->findOrFail($id);
        return response()->json(['data' => $brand]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands')->where(fn ($query) => $query->where('organization_id', $request->user()->organization_id))
            ]
        ]);

        $validated['organization_id'] = $request->user()->organization_id;
        $brand = Brand::create($validated);

        return response()->json(['message' => 'Marca registrada correctamente.', 'data' => $brand], 201);
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::where('organization_id', $request->user()->organization_id)->findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands')->ignore($brand->id)->where(fn ($query) => $query->where('organization_id', $request->user()->organization_id))
            ]
        ]);

        $brand->update($validated);

        return response()->json(['message' => 'Marca actualizada correctamente.', 'data' => $brand]);
    }

    public function destroy(Request $request, $id)
    {
        $brand = Brand::where('organization_id', $request->user()->organization_id)->findOrFail($id);
        $brand->delete();

        return response()->json(['message' => 'Marca eliminada correctamente.']);
    }
}