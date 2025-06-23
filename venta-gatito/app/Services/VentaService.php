<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class VentaService
{
    public function obtenerVentas()
    {
        return Venta::with('detalles')->orderBy('id', 'desc')->get();
    }

    public function registrarVenta(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            $venta = Venta::create([
                'cliente_id' => $data['cliente_id'],
                'usuario_id' => $data['usuario_id'],
                'fecha' => $data['fecha'],
                'total' => 0,
            ]);

            $total = 0;
            foreach ($data['productos'] as $detalle) {
                $producto = Producto::find($detalle['producto_id']);
                if (!$producto) {
                    throw new \Exception("Producto no encontrado.");
                }

                $inventario = Inventario::where('producto_id', $detalle['producto_id'])->first();
                if (!$inventario || $inventario->stock_actual < $detalle['cantidad']) {
                    throw new \Exception("Stock insuficiente para producto ID {$detalle['producto_id']}");
                }

                $precioUnitario = $producto->precio;
                $subtotal = $detalle['cantidad'] * $precioUnitario;

                $inventario->stock_actual -= $detalle['cantidad'];
                $inventario->save();

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $venta->total = $total;
            $venta->save();

            return $venta->fresh(['cliente', 'usuario', 'detalles']);
        });
    }

    public function actualizarVenta($id, array $data): ?Venta
    {
        return DB::transaction(function () use ($id, $data) {
            $venta = Venta::find($id);
            if (!$venta) {
                return null;
            }

            $venta->update([
                'cliente_id' => $data['cliente_id'] ?? $venta->cliente_id,
                'usuario_id' => $data['usuario_id'] ?? $venta->usuario_id,
                'fecha' => $data['fecha'] ?? $venta->fecha,
            ]);

            // Actualizar detalles (opcional: puedes agregar lógica para actualizar detalles)

            return $venta->fresh('detalles');
        });
    }

    public function eliminarVenta($id): bool
    {
        return DB::transaction(function () use ($id) {
            $venta = Venta::find($id);
            if (!$venta) {
                return false;
            }

            // Devolver stock de cada detalle
            foreach ($venta->detalles as $detalle) {
                $inventario = Inventario::where('producto_id', $detalle->producto_id)->first();
                if ($inventario) {
                    $inventario->stock_actual += $detalle->cantidad;
                    $inventario->save();
                }
                $detalle->delete();
            }

            $venta->delete();

            return true;
        });
    }
}
