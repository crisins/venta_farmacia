<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Inventario;
use App\Services\DetalleVentaService;
use Illuminate\Support\Facades\DB;
class VentaService
{
    protected $detalleVentaService;

    public function __construct(DetalleVentaService $detalleVentaService)
    {
        $this->detalleVentaService = $detalleVentaService;
    }
    #POST
    public function registrarVenta(array $data): Venta
    {
        DB::beginTransaction();

        try {
            // 1. Crear venta (sin total todavía)
            $venta = Venta::create([
                'cliente_id' => $data['cliente_id'],
                'usuario_id' => $data['usuario_id'],
                'fecha'      => $data['fecha'],
                'total'      => 0
            ]);

            $total = 0;

            // 2. Crear cada detalle y acumular subtotal
            foreach ($data['productos'] as $producto) {
                $detalle = $this->detalleVentaService->crearDetalle([
                    'venta_id'    => $venta->id,
                    'producto_id' => $producto['producto_id'],
                    'cantidad'    => $producto['cantidad'],
                ]);

                $total += $detalle->subtotal;
            }

            // 3. Actualizar total de la venta
            $venta->total = $total;
            $venta->save();

            DB::commit();
            return $venta;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    #GETALL
    public function obtenerVentas()
    {
        return Venta::orderBy('fecha', 'desc')->get();
    }
    #GET ID
    public function obtenerVentaPorId($id)
    {
        $venta = Venta::find($id);

        if (!$venta) {
            throw new \Exception("Venta no encontrada.");
        }

        return $venta;
    }
    #PUT
    public function actualizarVenta($id, array $data): ?Venta
    {
        $venta = Venta::where('id', $id)->first();

        if (!$venta) {
            return null;
        }

        DB::beginTransaction();

        try {
            // 1. Actualizar datos generales
            $venta->update([
                'cliente_id' => $data['cliente_id'],
                'usuario_id' => $data['usuario_id'],
                'fecha'      => $data['fecha'],
            ]);

            $total = 0;

            // 2. Si viene arreglo de productos, reemplazar detalles
            if (!empty($data['productos'])) {
                $detallesAnteriores = DetalleVenta::where('venta_id', $venta->id)->get();

                // a) Devolver stock y eliminar detalles
                foreach ($detallesAnteriores as $detalle) {
                    $inventario = Inventario::where('producto_id', $detalle->producto_id)->first();
                    if ($inventario) {
                        $inventario->stock_actual += $detalle->cantidad;
                        $inventario->save();
                    }
                    $detalle->delete();
                }

                // b) Crear nuevos detalles
                foreach ($data['productos'] as $producto) {
                    $nuevoDetalle = $this->detalleVentaService->crearDetalle([
                        'venta_id'    => $venta->id,
                        'producto_id' => $producto['producto_id'],
                        'cantidad'    => $producto['cantidad'],
                    ]);
                    $total += $nuevoDetalle->subtotal;
                }

                // c) Actualizar total con base en los nuevos detalles
                $venta->update(['total' => $total]);
            }

            DB::commit();
            return $venta;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    #DELETE
    public function eliminarVenta($id): bool
    {

        $venta = Venta::find($id);

        if (!$venta) {
            return false;
        }

        DB::beginTransaction();

        try {
            // Obtener todos los detalles asociados
            $detalles = DetalleVenta::where('venta_id', $venta->id)->get();

            foreach ($detalles as $detalle) {
                // Devolver cantidad al inventario
                $inventario = Inventario::where('producto_id', $detalle->producto_id)->first();

                if ($inventario) {
                    $inventario->stock_actual += $detalle->cantidad;
                    $inventario->save();
                }

                // Eliminar el detalle
                $detalle->delete();
            }

            // Eliminar la venta
            $venta->delete();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

}
