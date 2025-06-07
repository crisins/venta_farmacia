<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetallePedido;

class DetallePedidoController extends Controller
{
    public function index()
{
    $detalles = DetallePedido::with(['pedido.usuario', 'producto'])->get();
    return response()->json($detalles);
}


    public function show($id)
    {
        $detallePedido = DetallePedido::find($id);
        if ($detallePedido) {
            return response()->json($detallePedido);
        } else {
            return response()->json(['error' => 'Detalle del pedido no encontrado'], 404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',  // El pedido debe existir
            'producto_id' => 'required|exists:productos,id',  // El producto debe existir
            'cantidad' => 'required|integer|min:1',  // La cantidad debe ser un número entero mayor que 0
            'precio_unit' => 'required|numeric|min:0',  // El precio unitario debe ser un número mayor o igual a 0
        ]);

        $detallePedido = DetallePedido::create([
            'pedido_id' => $request->pedido_id,
            'producto_id' => $request->producto_id,
            'cantidad' => $request->cantidad,
            'precio_unit' => $request->precio_unit,
        ]);

        return response()->json($detallePedido, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',  // El pedido debe existir
            'producto_id' => 'required|exists:productos,id',  // El producto debe existir
            'cantidad' => 'required|integer|min:1',  // La cantidad debe ser un número entero mayor que 0
            'precio_unit' => 'required|numeric|min:0',  // El precio unitario debe ser un número mayor o igual a 0
        ]);

        $detallePedido = DetallePedido::find($id);

        if ($detallePedido) {
            $detallePedido->update([
                'pedido_id' => $request->pedido_id,
                'producto_id' => $request->producto_id,
                'cantidad' => $request->cantidad,
                'precio_unit' => $request->precio_unit,
            ]);

            return response()->json($detallePedido);
        } else {
            return response()->json(['error' => 'Detalle del pedido no encontrado'], 404);
        }
    }

    public function destroy($id)
    {

        $detallePedido = DetallePedido::find($id);

        if ($detallePedido) {
            $detallePedido->delete();

            return response()->json(['message' => 'Detalle del pedido eliminado']);
        } else {
            return response()->json(['error' => 'Detalle del pedido no encontrado'], 404);
        }
    }
}
