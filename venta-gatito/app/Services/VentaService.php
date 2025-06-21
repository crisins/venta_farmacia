<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto; // Importar el modelo Producto
use App\Models\Inventario; // Importar el modelo Inventario
use Illuminate\Support\Facades\DB;
use InvalidArgumentException; // Asegúrate de que esta línea esté presente

class VentaService
{
    public function obtenerVentas()
    {
        return Venta::with(['cliente', 'usuario', 'detalles.producto'])->get();
    }

    public function registrarVenta(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            $venta = Venta::create([
                'cliente_id' => $data['cliente_id'],
                'usuario_id' => $data['usuario_id'],
                'fecha' => $data['fecha'],
                'total' => 0, // Se calculará después de añadir detalles
            ]);

            $totalVenta = 0;
            foreach ($data['productos'] as $productoData) {
                $producto = Producto::find($productoData['producto_id']);
                $inventario = Inventario::where('producto_id', $productoData['producto_id'])->first();

                if (!$producto || !$inventario) {
                    throw new InvalidArgumentException("Producto o inventario no encontrado para ID: {$productoData['producto_id']}.");
                }

                if ($inventario->stock_actual < $productoData['cantidad']) {
                    throw new InvalidArgumentException('Stock insuficiente para el producto ID ' . $productoData['producto_id'] . '. Stock disponible: ' . $inventario->stock_actual);
                }

                $subtotal = $productoData['cantidad'] * $producto->precio;
                $venta->detalles()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $productoData['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'subtotal' => $subtotal,
                ]);

                // Reducir el stock
                $inventario->stock_actual -= $productoData['cantidad'];
                $inventario->save();

                $totalVenta += $subtotal;
            }

            $venta->total = $totalVenta;
            $venta->save();

            return $venta->fresh(['cliente', 'usuario', 'detalles.producto']);
        });
    }

    public function actualizarVenta($id, array $data): ?Venta
    {
        return DB::transaction(function () use ($id, $data) {
            $venta = Venta::with('detalles')->find($id); // Cargar los detalles existentes
            if (!$venta) {
                return null; // O lanzar una excepción InvalidArgumentException
            }

            // Mapear los detalles existentes por producto_id para fácil acceso
            $existingDetails = $venta->detalles->keyBy('producto_id');
            $newTotal = 0;
            $productsToKeep = []; // Para llevar un registro de los productos que permanecen o se añaden

            // Procesar los productos enviados en la actualización
            foreach ($data['productos'] as $newProductData) {
                $productoId = $newProductData['producto_id'];
                $newQuantity = $newProductData['cantidad'];
                $productsToKeep[] = $productoId; // Marcar este producto como "a mantener"

                $producto = Producto::find($productoId);
                if (!$producto) {
                    throw new InvalidArgumentException("Producto con ID {$productoId} no encontrado.");
                }
                $inventario = Inventario::where('producto_id', $productoId)->first();
                if (!$inventario) {
                    throw new InvalidArgumentException("Inventario no encontrado para producto ID {$productoId}.");
                }

                $oldDetail = $existingDetails->get($productoId);

                if ($oldDetail) {
                    // El producto ya existía en la venta
                    $oldQuantity = $oldDetail->cantidad;
                    $stockChange = $oldQuantity - $newQuantity; // Positivo si la cantidad se redujo (stock aumenta), Negativo si la cantidad aumentó (stock disminuye)

                    if ($stockChange !== 0) {
                        if ($stockChange < 0) { // La cantidad aumentó, deducir más stock
                            $requiredStock = abs($stockChange); // Cantidad adicional necesaria
                            if ($inventario->stock_actual < $requiredStock) {
                                throw new InvalidArgumentException("Stock insuficiente para el producto ID {$productoId}. Necesitas {$requiredStock} unidades adicionales, disponibles: {$inventario->stock_actual}.");
                            }
                            $inventario->stock_actual -= $requiredStock; // Restar la cantidad adicional
                        } else { // La cantidad disminuyó, devolver stock
                            $inventario->stock_actual += $stockChange; // Sumar la cantidad devuelta
                        }
                        $inventario->save();
                    }

                    // Actualizar el detalle existente
                    $oldDetail->update([
                        'cantidad' => $newQuantity,
                        'precio_unitario' => $producto->precio, // Usar precio actual del producto
                        'subtotal' => $newQuantity * $producto->precio,
                    ]);
                    $newTotal += $oldDetail->subtotal;

                } else {
                    // Es un producto nuevo añadido a la venta
                    if ($inventario->stock_actual < $newQuantity) {
                        throw new InvalidArgumentException("Stock insuficiente para el nuevo producto ID {$productoId}. Necesitas {$newQuantity}, disponible: {$inventario->stock_actual}.");
                    }
                    $inventario->stock_actual -= $newQuantity;
                    $inventario->save();

                    $newDetail = $venta->detalles()->create([
                        'producto_id' => $productoId,
                        'cantidad' => $newQuantity,
                        'precio_unitario' => $producto->precio,
                        'subtotal' => $newQuantity * $producto->precio,
                    ]);
                    $newTotal += $newDetail->subtotal;
                }
            }

            // Eliminar detalles de productos que fueron removidos de la venta original
            foreach ($existingDetails as $oldDetail) {
                if (!in_array($oldDetail->producto_id, $productsToKeep)) {
                    $inventario = Inventario::where('producto_id', $oldDetail->producto_id)->first();
                    if ($inventario) {
                        $inventario->stock_actual += $oldDetail->cantidad; // Devolver stock
                        $inventario->save();
                    }
                    $oldDetail->delete(); // Eliminar el detalle
                }
            }

            // Actualizar información básica de la venta y el nuevo total
            $venta->update([
                'cliente_id' => $data['cliente_id'] ?? $venta->cliente_id,
                'usuario_id' => $data['usuario_id'] ?? $venta->usuario_id,
                'fecha' => $data['fecha'] ?? $venta->fecha,
                'total' => $newTotal, // ¡Actualizar el total!
            ]);

            return $venta->fresh(['detalles.producto', 'cliente', 'usuario']); // Recargar relaciones
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