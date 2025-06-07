<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RecetaMedica;
use App\Models\Usuario;

class RecetaMedicaController extends Controller
{
    public function index()
    {
        return response()->json(RecetaMedica::with('usuario')->get());
    }

    public function show($id)
    {
        $receta = RecetaMedica::with('usuario')->find($id);
        if ($receta) {
            return response()->json($receta);
        } else {
            return response()->json(['error' => 'Receta médica no encontrada'], 404);
        }
    }

    public function store(Request $request)
    {
        $receta = RecetaMedica::create([
            'usuario_id' => $request->usuario_id,
            'archivo_url' => $request->archivo_url,
            'fecha_subida' => $request->fecha_subida,
            'estado_validacion' => $request->estado_validacion,
        ]);

        return response()->json($receta, 201);
    }

    public function update(Request $request, $id)
    {
        $receta = RecetaMedica::find($id);
        if (!$receta) {
            return response()->json(['error' => 'Receta médica no encontrada'], 404);
        }

        $receta->update($request->only([
            'archivo_url',
            'fecha_subida',
            'estado_validacion',
        ]));

        return response()->json($receta);
    }
}
