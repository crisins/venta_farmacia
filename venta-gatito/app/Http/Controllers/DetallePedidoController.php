<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetallePedido;

class DetallePedidoController extends Controller
{
    // Listar todos los detalles de pedido
    public function index()
    {
        $detalles = DetallePedido::with(['pedido', 'productoProveedor'])->get();
        return response()->json($detalles);
    }

    // Mostrar un detalle de pedido específico
    public function show($id)
    {
        $detalle = DetallePedido::with(['pedido', 'productoProveedor'])->find($id);
        if ($detalle) {
            return response()->json($detalle);
        } else {
            return response()->json(['error' => 'Detalle del pedido no encontrado'], 404);
        }
    }

    // Crear un nuevo detalle de pedido
    public function store(Request $request)
    {
        $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',
            'producto_proveedor_id' => 'required|exists:productos_proveedores,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unit' => 'required|numeric|min:0',
        ]);

        $detalle = DetallePedido::create($request->all());
        return response()->json($detalle, 201);
    }

    // Actualizar un detalle de pedido
    public function update(Request $request, $id)
    {
        $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',
            'producto_proveedor_id' => 'required|exists:productos_proveedores,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unit' => 'required|numeric|min:0',
        ]);

        $detalle = DetallePedido::find($id);
        if ($detalle) {
            $detalle->update($request->all());
            return response()->json($detalle);
        } else {
            return response()->json(['error' => 'Detalle del pedido no encontrado'], 404);
        }
    }

    // Eliminar un detalle de pedido
    public function destroy($id)
    {
        $detalle = DetallePedido::find($id);
        if ($detalle) {
            $detalle->delete();
            return response()->json(['message' => 'Detalle del pedido eliminado']);
        } else {
            return response()->json(['error' => 'Detalle del pedido no encontrado'], 404);
        }
    }
}
