<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Mostrar todos los Categorias.
     */
    public function index()
    {
        return response()->json(Categoria::all(), 200);
    }

    /**
     * Almacenar un nuevo Categoria.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
        ]);

        $categoria = Categoria::create($validatedData);

        return response()->json($categoria, 201);
    }

    /**
     * Mostrar un Categoria específico.
     */
    public function show(Categoria $Categoria)
    {
        return response()->json($Categoria, 200);
    }

    /**
     * Actualizar un Categoria.
     */
    public function update(Request $request, Categoria $Categoria)
    {
        $validatedData = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'sometimes|required|string',
        ]);

        $Categoria->update($validatedData);

        return response()->json($Categoria, 200);
    }

    /**
     * Eliminar un Categoria.
     */
    public function destroy(Categoria $Categoria)
    {

        $Categoria->delete();

        return response()->json(null, 204);
    }
}
