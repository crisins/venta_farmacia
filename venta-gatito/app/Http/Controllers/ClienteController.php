<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Cliente::all(),
            'message' => 'Lista de clientes obtenida correctamente'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:clientes',
            'telefono' => 'nullable|string|max:20'
        ]);

        $cliente = Cliente::create($validated);

        return response()->json([
            'data' => $cliente,
            'message' => 'Cliente creado exitosamente'
        ], 201);
    }

    public function show(Cliente $cliente)
    {
        return response()->json([
            'data' => $cliente,
            'message' => 'Cliente obtenido correctamente'
        ]);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:clientes,email,'.$cliente->id,
            'telefono' => 'nullable|string|max:20'
        ]);

        $cliente->update($validated);

        return response()->json([
            'data' => $cliente,
            'message' => 'Cliente actualizado correctamente'
        ]);
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return response()->json([
            'message' => 'Cliente eliminado correctamente'
        ], 204);
    }
}