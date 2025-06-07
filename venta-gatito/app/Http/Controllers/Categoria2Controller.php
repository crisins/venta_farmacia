<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Categoria2controller extends Controller
{
public function index()
{
    // Lógica para obtener todas las categorías
    return response()->json(['message' => 'Listar todas las categorías']);
}
public function store(Request $request)
{
    // Lógica para almacenar una nueva categoría
    return response()->json(['message' => 'Almacenar una nueva categoría']);
}
public function show($id)
{
    // Lógica para mostrar una categoría específica
    return response()->json(['message' => 'Mostrar categoría con ID: ' . $id]);
}
public function update(Request $request, $id)
{
    // Lógica para actualizar una categoría específica
    return response()->json(['message' => 'Actualizar categoría con ID: ' . $id]);
}
public function destroy($id)
{
    // Lógica para eliminar una categoría específica
    return response()->json(['message' => 'Eliminar categoría con ID: ' . $id]);
}
}
