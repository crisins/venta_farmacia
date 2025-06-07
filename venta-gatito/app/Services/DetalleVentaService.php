<?php

namespace App\Services;

use App\Models\DetalleVenta;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Venta;

class DetalleVentaService
{
    #POST
    public function crearDetalle(array $data): ?DetalleVenta
    {
        $inventario = Inventario::where('producto_id', $data['producto_id'])->first();
        if (!$inventario) {
            throw new \Exception("No se encontró inventario para el producto ID {$data['producto_id']}");
        }

        if ($inventario->stock_actual < $data['cantidad']) {
            throw new \Exception("Stock insuficiente para el producto ID {$data['producto_id']}");
        }

        $producto = Producto::find($data['producto_id']);
        if (!$producto) {
            throw new \Exception("Producto no encontrado.");
        }

        $precio_unitario = $producto->precio;
        $subtotal = $data['cantidad'] * $precio_unitario;

        $inventario->stock_actual -= $data['cantidad'];
        $inventario->save();

        // 1. Crear el detalle
        $detalle = DetalleVenta::create([
            'venta_id'        => $data['venta_id'],
            'producto_id'     => $data['producto_id'],
            'cantidad'        => $data['cantidad'],
            'precio_unitario' => $precio_unitario,
            'subtotal'        => $subtotal,
        ]);

        // 2. Sumar al total de la venta
        $venta = Venta::find($data['venta_id']);
        if ($venta) {
            $venta->total += $subtotal;
            $venta->save();
        }

        return $detalle;
    }
    #GETALL
    public function obtenerTodos()
    {
        return DetalleVenta::orderBy('id', 'desc')->get();
    }
    #GET ID
    public function obtenerPorId($id)
    {
        return DetalleVenta::find($id);
    }
    #PUT
    public function actualizarDetalle($id, array $data): ?DetalleVenta
    {
        $detalle = DetalleVenta::where('id', $id)->first();

        if (!$detalle) {
            return null;
        }

        // Devolver la cantidad anterior al inventario original
        $inventarioAnterior = Inventario::where('producto_id', $detalle->producto_id)->first();
        if ($inventarioAnterior) {
            $inventarioAnterior->stock += $detalle->cantidad;
            $inventarioAnterior->save();
        }

        // Obtener el precio del nuevo producto
        $producto = Producto::find($data['producto_id']);
        if (!$producto) {
            throw new \Exception("Producto no encontrado.");
        }

        $nuevoPrecio = $producto->precio;
        $nuevoSubtotal = $data['cantidad'] * $nuevoPrecio;

        // Descontar la nueva cantidad del inventario nuevo
        $inventarioNuevo = Inventario::where('producto_id', $data['producto_id'])->first();
        if (!$inventarioNuevo || $inventarioNuevo->stock < $data['cantidad']) {
            throw new \Exception("Stock insuficiente para el producto ID {$data['producto_id']}");
        }

        $inventarioNuevo->stock -= $data['cantidad'];
        $inventarioNuevo->save();

        // Actualizar el detalle
        $detalle->update([
            'producto_id'     => $data['producto_id'],
            'cantidad'        => $data['cantidad'],
            'precio_unitario' => $nuevoPrecio,
            'subtotal'        => $nuevoSubtotal,
        ]);

        return $detalle;
    }

    #DELETE
    public function eliminarDetalle($id): bool
    {
        $detalle = DetalleVenta::find($id);

        if (!$detalle) {
            return false;
        }

        // 1. Devolver cantidad al inventario
        $inventario = Inventario::where('producto_id', $detalle->producto_id)->first();
        if ($inventario) {
            $inventario->stock_actual += $detalle->cantidad;
            $inventario->save();
        }

        $ventaId = $detalle->venta_id;

        // 2. Eliminar el detalle
        $detalle->delete();

        // 3. Recalcular total de la venta
        $venta = Venta::find($ventaId);
        if ($venta) {
            $nuevoTotal = $venta->detalles()->sum('subtotal');
            $venta->total = $nuevoTotal;
            $venta->save();
        }

        return true;
    }
}

