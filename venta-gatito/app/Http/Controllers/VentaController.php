<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Services\VentaService;
use App\Http\Requests\VentaRequest;
use App\Models\Venta;
use InvalidArgumentException; 

class VentaController extends Controller
{
    protected $ventaService;

    public function __construct(VentaService $ventaService)
    {
        $this->ventaService = $ventaService;
    }

    public function index(): JsonResponse
    {
        $ventas = $this->ventaService->obtenerVentas();

        // Formatear la respuesta para que sea más ordenada y legible
        $ventasFormateadas = $ventas->map(function ($venta) {
            return [
                'id' => $venta->id,
                'fecha' => $venta->fecha,
                'total' => $venta->total,
                'usuario' => [
                    'id' => $venta->usuario->id,
                    'nombre' => $venta->usuario->nombre,
                    'email' => $venta->usuario->email,
                    'tipo' => $venta->usuario->tipo,
                    'telefono' => $venta->usuario->telefono,
                    'direccion' => $venta->usuario->direccion,
                ],
                'detalles' => collect($venta->detalles)->map(function ($detalle) {
                    return [
                        'producto' => [
                            'id' => $detalle->producto->id,
                            'nombre' => $detalle->producto->nombre,
                            'descripcion' => $detalle->producto->descripcion,
                            'precio' => $detalle->producto->precio,
                            'requiere_receta' => $detalle->producto->requiere_receta,
                        ],
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'subtotal' => $detalle->subtotal,
                    ];
                })
            ];
        });

        return response()->json([
            'message' => 'Lista de ventas',
            'data' => $ventasFormateadas
        ]);
    }

    public function store(VentaRequest $request): JsonResponse
    {
        try {
            $venta = $this->ventaService->registrarVenta($request->validated());

            return response()->json([
                'message' => 'Venta registrada correctamente',
                'data' => $venta
            ], 201);

        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => 'Error de validación o lógica de negocio: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error inesperado al registrar venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $venta = Venta::with('detalles.producto', 'usuario')->find($id);
            if (!$venta) {
                return response()->json(['message' => 'Venta no encontrada'], 404);
            }
            $ventaFormateada = [
                'id' => $venta->id,
                'fecha' => $venta->fecha,
                'total' => $venta->total,
                'usuario' => [
                    'id' => $venta->usuario->id,
                    'nombre' => $venta->usuario->nombre,
                    'email' => $venta->usuario->email,
                    'tipo' => $venta->usuario->tipo,
                    'telefono' => $venta->usuario->telefono,
                    'direccion' => $venta->usuario->direccion,
                ],
                'detalles' => collect($venta->detalles)->map(function ($detalle) {
                    return [
                        'producto' => [
                            'id' => $detalle->producto->id,
                            'nombre' => $detalle->producto->nombre,
                            'descripcion' => $detalle->producto->descripcion,
                            'precio' => $detalle->producto->precio,
                            'requiere_receta' => $detalle->producto->requiere_receta,
                        ],
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'subtotal' => $detalle->subtotal,
                    ];
                })
            ];
            return response()->json([
                'message' => 'Venta obtenida correctamente',
                'data' => $ventaFormateada
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener venta',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function update(VentaRequest $request, $id): JsonResponse
    {
        try {
            $venta = $this->ventaService->actualizarVenta($id, $request->validated());

            if (!$venta) {
                return response()->json(['message' => 'Venta no encontrada.'], 404);
            }

            return response()->json([
                'message' => 'Venta actualizada correctamente',
                'data' => $venta
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => 'Error en la actualización de la venta: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error inesperado al actualizar venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $success = $this->ventaService->eliminarVenta($id);

            if (!$success) {
                return response()->json(['message' => 'Venta no encontrada o no se pudo eliminar'], 404);
            }

            return response()->json(['message' => 'Venta eliminada exitosamente']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar venta',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}