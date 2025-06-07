<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MetodoPago;

class MetodoPagoController extends Controller
{
    public function index()
    {
        $metodos = MetodoPago::all();
        return response()->json($metodos);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|unique:metodos_pago,nombre|max:100',
        ]);
    
        $metodo = MetodoPago::create($validated);
    
        return response()->json($metodo, 201);
    }
    public function show($id)
    {
        $metodo = MetodoPago::find($id);
    
        if (!$metodo) {
            return response()->json([
                'mensaje' => 'Método de pago no encontrado'
            ], 404);
        }
    
        return response()->json($metodo, 200);
    }
    public function update(Request $request, $id)
    {
        $metodo = MetodoPago::find($id);
    
        if (!$metodo) {
            return response()->json([
                'mensaje' => 'Método de pago no encontrado'
            ], 404);
        }
    
        // Validación
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:metodos_pago,nombre,' . $id,
        ]);
    
        // Actualización
        $metodo->update($validated);
    
        return response()->json([
            'mensaje' => 'Método de pago actualizado correctamente',
            'data' => $metodo
        ], 200);
    }
    public function destroy($id)
    {
        $metodo = MetodoPago::find($id);
    
        if (!$metodo) {
            return response()->json([
                'mensaje' => 'Método de pago no encontrado'
            ], 404);
        }
    
        $metodo->delete();
    
        return response()->json([
            'mensaje' => 'Método de pago eliminado correctamente'
        ], 200);
    }
    
    
}