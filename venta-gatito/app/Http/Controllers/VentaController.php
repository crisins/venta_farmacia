<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Services\VentaService;
use App\Http\Requests\VentaRequest;
use App\Models\Venta;
use InvalidArgumentException; // Importar para capturar errores específicos del servicio

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

        return response()->json([
            'message' => 'Lista de ventas',
            'data' => $ventas
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

        } catch (InvalidArgumentException $e) { // Captura específicamente esta excepción del servicio
            return response()->json([
                'message' => 'Error de validación o lógica de negocio: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 400); // 400 Bad Request es apropiado para datos lógicamente inválidos
        } catch (\Exception $e) { // Captura cualquier otra excepción
            return response()->json([
                'message' => 'Error inesperado al registrar venta',
                'error' => $e->getMessage()
            ], 500); // Un 500 Internal Server Error es más apropiado para errores inesperados
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $venta = Venta::with('detalles')->find($id);
            if (!$venta) {
                return response()->json(['message' => 'Venta no encontrada.'], 404);
            }
            return response()->json([
                'message' => 'Detalle de la venta',
                'data' => $venta
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
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
                'message' => 'Error en la actualización de la venta',
                'error' => $e->getMessage()
            ], 400);
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
            $eliminada = $this->ventaService->eliminarVenta($id);

            if (!$eliminada) {
                return response()->json(['message' => 'Venta no encontrada.'], 404);
            }

            return response()->json(['message' => 'Venta eliminada correctamente.']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}