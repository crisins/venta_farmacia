<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Producto;
use App\Models\Inventario;
use App\Models\DetalleVenta;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException; // Para excepciones de argumentos inválidos

class VentaService
{
    public function obtenerVentas()
    {
        return Venta::with('detalles')->orderBy('id', 'desc')->get();
    }

    public function registrarVenta(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            // **Capa de seguridad del servicio:**
            // Aunque VentaRequest.php ya tiene 'productos.min:1',
            // esta validación protege si el servicio es llamado directamente
            // o si por alguna razón la validación del request es omitida.
            if (empty($data['productos'])) {
                throw new InvalidArgumentException("La venta debe contener al menos un producto.");
            }

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
                    throw new InvalidArgumentException("Producto no encontrado con ID: {$detalle['producto_id']}."); // Cambio de \Exception a InvalidArgumentException
                }

                $inventario = Inventario::where('producto_id', $detalle['producto_id'])->first();
                if (!$inventario) { // O si no hay stock (este es un doble chequeo)
                    throw new InvalidArgumentException("No se encontró inventario para el producto ID {$detalle['producto_id']}.");
                }
                if ($inventario->stock_actual < $detalle['cantidad']) {
                    throw new InvalidArgumentException("Stock insuficiente para el producto ID {$detalle['producto_id']}. Stock actual: {$inventario->stock_actual}, solicitado: {$detalle['cantidad']}."); // Cambio de \Exception a InvalidArgumentException
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

            // TODO: Lógica para actualizar detalles y ajustar stock.
            // Esto es crucial para la funcionalidad de actualización completa.

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