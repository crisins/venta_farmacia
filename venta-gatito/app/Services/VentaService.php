<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVenta;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VentaService
{
    public function obtenerVentas()
    {
        return Venta::with(['usuario', 'detalles.producto'])->get();
    }

    public function registrarVenta(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            $venta = Venta::create([
                'usuario_id' => $data['usuario_id'],
                'fecha' => $data['fecha'],
                'total' => 0,
            ]);
            $totalVenta = 0;
            foreach ($data['productos'] as $productoData) {
                $producto = \App\Models\Producto::find($productoData['producto_id']);
                if (!$producto) {
                    throw new InvalidArgumentException("Producto no encontrado para ID: {$productoData['producto_id']}.");
                }
                if ($producto->requiere_receta && (empty($productoData['con_receta']) || $productoData['con_receta'] !== true)) {
                    throw new InvalidArgumentException('El producto requiere receta médica.');
                }
                if ($producto->stock < $productoData['cantidad']) {
                    throw new InvalidArgumentException("Stock insuficiente para el producto ID {$producto->id}. Disponible: {$producto->stock}.");
                }
                $subtotal = $productoData['cantidad'] * $producto->precio;
                $totalVenta += $subtotal;
                \App\Models\DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $productoData['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $subtotal,
                ]);
                $producto->stock -= $productoData['cantidad'];
                $producto->save();
            }
            $venta->update(['total' => $totalVenta]);
            return $venta->fresh(['detalles.producto', 'usuario']);
        });
    }

    public function actualizarVenta($id, array $data): ?Venta
    {
        return DB::transaction(function () use ($id, $data) {
            $venta = Venta::with('detalles')->find($id);
            if (!$venta) {
                return null;
            }
            $existingDetails = $venta->detalles;
            $productosAConservar = [];
            $newTotal = 0;
            foreach ($data['productos'] as $item) {
                $productoId = $item['producto_id'];
                $cantidadNueva = $item['cantidad'];
                $detalleExistente = $existingDetails->where('producto_id', $productoId)->first();
                $producto = \App\Models\Producto::find($productoId);
                if (!$producto) {
                    throw new InvalidArgumentException("Producto no encontrado para ID: {$productoId}.");
                }
                if ($producto->requiere_receta && (empty($item['con_receta']) || $item['con_receta'] !== true)) {
                    throw new InvalidArgumentException('El producto requiere receta médica.');
                }
                if ($detalleExistente) {
                    $diferencia = $cantidadNueva - $detalleExistente->cantidad;
                    if ($producto->stock < $diferencia) {
                        throw new InvalidArgumentException("Stock insuficiente para el producto ID {$productoId}. Disponible: {$producto->stock}.");
                    }
                    $producto->stock -= $diferencia;
                    $producto->save();
                    $detalleExistente->update([
                        'cantidad' => $cantidadNueva,
                        'precio_unitario' => $producto->precio,
                        'subtotal' => $cantidadNueva * $producto->precio,
                    ]);
                    $newTotal += $detalleExistente->subtotal;
                    $productosAConservar[] = $productoId;
                } else {
                    if ($producto->stock < $cantidadNueva) {
                        throw new InvalidArgumentException("Stock insuficiente para el producto ID {$productoId}. Disponible: {$producto->stock}.");
                    }
                    $producto->stock -= $cantidadNueva;
                    $producto->save();
                    $nuevoDetalle = \App\Models\DetalleVenta::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $productoId,
                        'cantidad' => $cantidadNueva,
                        'precio_unitario' => $producto->precio,
                        'subtotal' => $cantidadNueva * $producto->precio,
                    ]);
                    $newTotal += $nuevoDetalle->subtotal;
                }
            }
            foreach ($existingDetails as $detalle) {
                if (!in_array($detalle->producto_id, $productosAConservar)) {
                    $producto = \App\Models\Producto::find($detalle->producto_id);
                    if ($producto) {
                        $producto->stock += $detalle->cantidad;
                        $producto->save();
                    }
                    $detalle->delete();
                }
            }
            $venta->update([
                'usuario_id' => $data['usuario_id'] ?? $venta->usuario_id,
                'fecha' => $data['fecha'] ?? $venta->fecha,
                'total' => $newTotal,
            ]);
            return $venta->fresh(['detalles.producto', 'usuario']);
        });
    }

    public function eliminarVenta($id): bool
    {
        return DB::transaction(function () use ($id) {
            $venta = Venta::with('detalles')->find($id);
            if (!$venta) {
                return false;
            }
            foreach ($venta->detalles as $detalle) {
                $producto = \App\Models\Producto::find($detalle->producto_id);
                if ($producto) {
                    $producto->stock += $detalle->cantidad;
                    $producto->save();
                }
            }
            $venta->detalles()->delete();
            $venta->delete();
            return true;
        });
    }
}
