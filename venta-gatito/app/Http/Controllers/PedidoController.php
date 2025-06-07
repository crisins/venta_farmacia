<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;

class PedidoController extends Controller
{
    // Método para obtener todos los pedidos
    public function index()
    {
        return response()->json(Pedido::all());
    }

    // Método para obtener un pedido por ID
    public function show($id)
    {
        $pedido = Pedido::find($id);
        if ($pedido) {
            return response()->json($pedido);
        } else {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }
    }

    // Método para crear un nuevo pedido
    public function store(Request $request)
    {
        $pedido = Pedido::create([
            'usuario_id' => $request->usuario_id,
            'fecha_pedido' => $request->fecha_pedido,
            'estado' => $request->estado,
            'total' => $request->total,
        ]);

        return response()->json($pedido, 201);
    }

    // Método para actualizar un pedido
    public function update(Request $request, $id)
    {
        $pedido = Pedido::find($id);

        if ($pedido) {
            $pedido->update([
                'usuario_id' => $request->usuario_id,
                'fecha_pedido' => $request->fecha_pedido,
                'estado' => $request->estado,
                'total' => $request->total,
            ]);

            return response()->json($pedido);
        } else {
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }
    }
}
