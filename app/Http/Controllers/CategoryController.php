<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller{
    //leer todas las categorias get api/categories
    public function index(){
        return response()->json(Category::withCount('assets')->latest()->get(), 200); //withCount para traer el número de equipos asociados
    }

    //guardar una nueva categoria post api/categories
    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'organization_id' => $request->user()->organization_id,
        ]);

        return response()->json([
            'message' => 'Categoría creada correctamente.',
            'category' => $category
        ], 201);
    }

    //mostrar una categoria por id get api/categories/{id}
    public function show(string $id){
        $category = Category::find($id);

        if(!$category){
            return response()->json(['message' => 'Categoría no encontrada.'], 404);
        }

        return response()->json($category, 200);
    }

    //actualizar una categoria put api/categories/{id}
    public function update(Request $request, string $id){
        $category = Category::find($id);

        if(!$category){
            return response()->json(['message' => 'Categoría no encontrada.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Categoría actualizada correctamente.',
            'category' => $category
        ], 200);
    }

    //eliminar una categoria delete api/categories/{id}
    public function destroy(string $id){
        $category = Category::find($id);

        if(!$category){
            return response()->json(['message' => 'Categoría no encontrada.'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Categoría eliminada correctamente.'], 200);
    }
}
