<?php

namespace App\Services;

use App\Models\DetalleVenta;
use App\Models\Venta;

class DetalleVentaService
{
    public function crearDetalle(array $data): ?DetalleVenta
    {
        $producto = \App\Models\Producto::find($data['producto_id']);
        if (!$producto) {
            throw new \Exception("Producto no encontrado.");
        }
        if ($producto->stock < $data['cantidad']) {
            throw new \InvalidArgumentException("Stock insuficiente para el producto ID {$data['producto_id']}. Disponible: {$producto->stock}.");
        }
        $precio_unitario = $producto->precio;
        $subtotal = $data['cantidad'] * $precio_unitario;
        $producto->stock -= $data['cantidad'];
        $producto->save();
        $detalle = DetalleVenta::create([
            'venta_id' => $data['venta_id'],
            'producto_id' => $data['producto_id'],
            'cantidad' => $data['cantidad'],
            'precio_unitario' => $precio_unitario,
            'subtotal' => $subtotal,
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
        return DetalleVenta::with(['venta', 'producto'])->get();
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
        $productoAnterior = \App\Models\Producto::find($detalle->producto_id);
        if ($productoAnterior) {
            $productoAnterior->stock += $detalle->cantidad;
            $productoAnterior->save();
        }
        $productoNuevo = \App\Models\Producto::find($data['producto_id']);
        if (!$productoNuevo) {
            throw new \Exception("Producto no encontrado.");
        }
        if ($productoNuevo->stock < $data['cantidad']) {
            throw new \InvalidArgumentException("Stock insuficiente para el producto ID {$data['producto_id']}. Disponible: {$productoNuevo->stock}.");
        }
        $nuevoPrecio = $productoNuevo->precio;
        $nuevoSubtotal = $data['cantidad'] * $nuevoPrecio;
        $productoNuevo->stock -= $data['cantidad'];
        $productoNuevo->save();
        $detalle->update([
            'producto_id' => $data['producto_id'],
            'cantidad' => $data['cantidad'],
            'precio_unitario' => $nuevoPrecio,
            'subtotal' => $nuevoSubtotal,
        ]);
        return $detalle;
    }

    public function eliminarDetalle($id): bool
    {
        $detalle = DetalleVenta::find($id);
        if (!$detalle) {
            return false;
        }
        $producto = \App\Models\Producto::find($detalle->producto_id);
        if ($producto) {
            $producto->stock += $detalle->cantidad;
            $producto->save();
        }
        $detalle->delete();
        return true;
    }
}
