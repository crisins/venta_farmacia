<?php

/*
 * EgresoService.php
 * Servicio para manejar la lógica de negocio relacionada con los egresos.
 * Este servicio se encarga de interactuar con el modelo Egreso y realizar operaciones CRUD.
 *
 * @package App\Services
 */

namespace App\Services;

use App\Models\Egreso;
use App\Models\DetalleEgreso;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EgresoService
{

    /**
     * Mostrar un egreso específico con sus detalles.
     */
    public function mostrarEgreso(int $egresoId)
    {
        return Egreso::with(['detalles.producto', 'proveedor', 'usuario'])->findOrFail($egresoId);
    }
    /**
     * Listar todos los egresos con sus detalles o filtros.
     */
    public function listarEgresos(?int $proveedorId = null, ?int $productoId = null, ?string $fechaInicio = null, ?string $fechaFin = null)
    {
        $query = Egreso::with(['proveedor', 'usuario']);

        if ($proveedorId) {
            $query->where('proveedor_id', $proveedorId);
        }

        if ($productoId) {
            $query->whereHas('detalles', function ($q) use ($productoId) {
                $q->where('producto_id', $productoId);
            });
        }

        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        } elseif ($fechaInicio) {
            $query->where('fecha', '>=', $fechaInicio);
        } elseif ($fechaFin) {
            $query->where('fecha', '<=', $fechaFin);
        }

        return $query->orderByDesc('fecha')->get();
    }
    /**
     * Registrar un nuevo egreso y actualizar el stock del producto.
     */
    public function registrarEgreso(array $data)
    {
        // Validación básica del request
        $validator = Validator::make($data, [
            'proveedor_id' => 'required|exists:proveedores,id',
            'usuario_id' => 'required|exists:usuarios,id',
            'fecha' => 'required|date',
            'tipo' => 'required|in:entrada,salida,ajuste',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'required|numeric|min:0', // Este es el precio/costo para el egreso
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($data) {
            $total = 0;
            $tipoEgreso = $data['tipo']; // Obtener el tipo de egreso

            $egreso = Egreso::create([
                'proveedor_id' => $data['proveedor_id'],
                'usuario_id' => $data['usuario_id'],
                'fecha' => $data['fecha'],
                'tipo' => $tipoEgreso,
                'total' => 0, // se actualiza luego
            ]);

            foreach ($data['productos'] as $item) {
                $subtotal = $item['cantidad'] * $item['precio_unitario'];
                $total += $subtotal;

                // Crear detalle
                DetalleEgreso::create([
                    'egreso_id' => $egreso->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'], // <--- CORREGIDO AQUÍ: Usar 'precio_unitario'
                    'subtotal' => $subtotal,
                ]);

                $producto = Producto::find($item['producto_id']);
                if (!$producto) {
                    throw new ValidationException("Producto no encontrado para egreso.");
                }

                // Lógica para actualizar stock basada en el tipo de egreso
                if ($tipoEgreso === 'entrada' || ($tipoEgreso === 'ajuste' && $item['cantidad'] > 0)) {
                    $producto->stock += $item['cantidad'];
                } elseif ($tipoEgreso === 'salida' || ($tipoEgreso === 'ajuste' && $item['cantidad'] < 0)) {
                    // Para una salida o ajuste negativo, verificar stock antes de restar
                    if ($producto->stock < $item['cantidad']) {
                        throw ValidationException::withMessages([
                            'productos.' . $item['producto_id'] => ['Stock insuficiente para la salida del producto ID ' . $item['producto_id'] . '. Stock disponible: ' . $producto->stock]
                        ]);
                    }
                    $producto->stock -= $item['cantidad'];
                }
                $producto->save();
            }

            // Actualizar total del egreso
            $egreso->update(['total' => $total]);

            return $egreso->load('detalles');
        });
    }
    /**
     * Actualizar un egreso existente y su stock.
     */
    public function actualizarEgreso(int $egresoId, array $data)
    {
        $validator = Validator::make($data, [
            'proveedor_id' => 'required|exists:proveedores,id',
            'usuario_id' => 'required|exists:usuarios,id',
            'fecha' => 'required|date',
            'tipo' => 'required|in:entrada,salida,ajuste',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'required|numeric|min:0', // Este es el precio/costo para el egreso
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($egresoId, $data) {
            $egreso = Egreso::with('detalles')->findOrFail($egresoId);
            $tipoNuevoEgreso = $data['tipo']; // Obtener el nuevo tipo de egreso

            // 1. REVERTIR STOCK ANTERIOR
            foreach ($egreso->detalles as $detalle) {
                $producto = Producto::find($detalle->producto_id);
                if ($producto) {
                    // Revertir según el tipo original del egreso (si se guardó)
                    $tipoOriginalEgreso = $egreso->tipo ?? 'entrada'; // Asume 'entrada' si no hay tipo guardado

                    if ($tipoOriginalEgreso === 'entrada' || ($tipoOriginalEgreso === 'ajuste' && $detalle->cantidad > 0)) {
                        $producto->stock -= $detalle->cantidad;
                    } elseif ($tipoOriginalEgreso === 'salida' || ($tipoOriginalEgreso === 'ajuste' && $detalle->cantidad < 0)) {
                        $producto->stock += $detalle->cantidad;
                    }
                    $producto->save();
                }
            }

            // 2. ELIMINAR DETALLES ANTERIORES
            $egreso->detalles()->delete();

            // 3. ACTUALIZAR ENCABEZADO
            $egreso->update([
                'proveedor_id' => $data['proveedor_id'],
                'usuario_id' => $data['usuario_id'],
                'fecha' => $data['fecha'],
                'tipo' => $tipoNuevoEgreso, // Actualizar el tipo de egreso
                'total' => 0, // recalculamos abajo
            ]);

            $total = 0;

            // 4. INGRESAR NUEVOS DETALLES Y ACTUALIZAR STOCK
            foreach ($data['productos'] as $item) {
                $subtotal = $item['cantidad'] * $item['precio_unitario'];
                $total += $subtotal;

                DetalleEgreso::create([
                    'egreso_id' => $egreso->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'], // <--- CORREGIDO AQUÍ: Usar 'precio_unitario'
                    'subtotal' => $subtotal,
                ]);

                $producto = Producto::find($item['producto_id']);
                if (!$producto) {
                    throw new ValidationException("Producto no encontrado para egreso.");
                }

                // Aplicar stock según el NUEVO tipo de egreso
                if ($tipoNuevoEgreso === 'entrada' || ($tipoNuevoEgreso === 'ajuste' && $item['cantidad'] > 0)) {
                    $producto->stock += $item['cantidad'];
                } elseif ($tipoNuevoEgreso === 'salida' || ($tipoNuevoEgreso === 'ajuste' && $item['cantidad'] < 0)) {
                    if ($producto->stock < $item['cantidad']) {
                        throw ValidationException::withMessages([
                            'productos.' . $item['producto_id'] => ['Stock insuficiente para la salida del producto ID ' . $item['producto_id'] . '. Stock disponible: ' . $producto->stock]
                        ]);
                    }
                    $producto->stock -= $item['cantidad'];
                }
                $producto->save();
            }

            $egreso->update(['total' => $total]);

            return $egreso->load('detalles');
        });
    }
    /**
     * Eliminar un egreso y revertir el stock.
     */
    public function eliminarEgreso(int $egresoId)
    {
        return DB::transaction(function () use ($egresoId) {
            $egreso = Egreso::with('detalles')->findOrFail($egresoId);

            // 1. Revertir el stock
            foreach ($egreso->detalles as $detalle) {
                $producto = Producto::find($detalle->producto_id);
                if ($producto) {
                    // Revertir el stock según el tipo de egreso que se elimina
                    $tipoEgresoOriginal = $egreso->tipo ?? 'entrada'; // Asume 'entrada' si no hay tipo guardado en el egreso

                    if ($tipoEgresoOriginal === 'entrada' || ($tipoEgresoOriginal === 'ajuste' && $detalle->cantidad > 0)) {
                        $producto->stock -= $detalle->cantidad;
                    } elseif ($tipoEgresoOriginal === 'salida' || ($tipoEgresoOriginal === 'ajuste' && $detalle->cantidad < 0)) {
                        $producto->stock += $detalle->cantidad;
                    }
                    $producto->save();
                }
            }

            // 2. Eliminar los detalles
            $egreso->detalles()->delete();

            // 3. Eliminar el egreso
            $egreso->delete();

            return true;
        });
    }
    /**
     * Obtener el total de egresos en un rango de fechas.
     */
    public function reportePorFechas(string $inicio, string $fin)
    {
        return Egreso::with(['proveedor', 'usuario'])
            ->whereBetween('fecha', [$inicio, $fin])
            ->orderBy('fecha', 'asc')
            ->get();
    }
}