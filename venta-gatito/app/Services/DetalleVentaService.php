<?php

namespace App\Services;

use App\Models\DetalleVenta;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Venta;

class DetalleVentaService
{
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

        $detalle = DetalleVenta::create([
            'venta_id'        => $data['venta_id'],
            'producto_id'     => $data['producto_id'],
            'cantidad'        => $data['cantidad'],
            'precio_unitario' => $precio_unitario,
            'subtotal'        => $subtotal,
        ]);

        $venta = Venta::find($data['venta_id']);
        if ($venta) {
            $venta->total += $subtotal;
            $venta->save();
        }

        return $detalle;
    }

    public function obtenerTodos()
    {
        return DetalleVenta::orderBy('id', 'desc')->get();
    }

    public function obtenerPorId($id)
    {
        return DetalleVenta::find($id);
    }

    public function actualizarDetalle($id, array $data): ?DetalleVenta
    {
        $detalle = DetalleVenta::where('id', $id)->first();

        if (!$detalle) {
            return null;
        }

        // Devolver cantidad anterior al inventario
        $inventarioAnterior = Inventario::where('producto_id', $detalle->producto_id)->first();
        if ($inventarioAnterior) {
            $inventarioAnterior->stock_actual += $detalle->cantidad;
            $inventarioAnterior->save();
        }

        $producto = Producto::find($data['producto_id']);
        if (!$producto) {
            throw new \Exception("Producto no encontrado.");
        }

        $nuevoPrecio = $producto->precio;
        $nuevoSubtotal = $data['cantidad'] * $nuevoPrecio;

        $inventarioNuevo = Inventario::where('producto_id', $data['producto_id'])->first();
        if (!$inventarioNuevo || $inventarioNuevo->stock_actual < $data['cantidad']) {
            throw new \Exception("Stock insuficiente para el producto ID {$data['producto_id']}");
        }

        $inventarioNuevo->stock_actual -= $data['cantidad'];
        $inventarioNuevo->save();

        $detalle->update([
            'producto_id'     => $data['producto_id'],
            'cantidad'        => $data['cantidad'],
            'precio_unitario' => $nuevoPrecio,
            'subtotal'        => $nuevoSubtotal,
        ]);

        return $detalle;
    }

    public function eliminarDetalle($id): bool
    {
        $detalle = DetalleVenta::find($id);

        if (!$detalle) {
            return false;
        }

        $inventario = Inventario::where('producto_id', $detalle->producto_id)->first();
        if ($inventario) {
            $inventario->stock_actual += $detalle->cantidad;
            $inventario->save();
        }

        $ventaId = $detalle->venta_id;
        $detalle->delete();

        $venta = Venta::find($ventaId);
        if ($venta) {
            $nuevoTotal = $venta->detalles()->sum('subtotal');
            $venta->total = $nuevoTotal;
            $venta->save();
        }

        return true;
    }
}
