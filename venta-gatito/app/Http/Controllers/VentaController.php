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

        } catch (InvalidArgumentException $e) { // Captura excepciones lanzadas por el servicio (ej. stock, lógica)
            return response()->json([
                'message' => 'Error de validación o lógica de negocio: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 422); // Un 422 (Unprocessable Entity) es apropiado para errores de validación de negocio.
        } catch (\Exception $e) { // Captura cualquier otra excepción inesperada
            // Puedes loggear el error aquí para depuración
            // Log::error("Error inesperado en VentaController@store: " . $e->getMessage());
            return response()->json([
                'message' => 'Error inesperado al registrar venta',
                'error' => $e->getMessage()
            ], 500); // Un 500 (Internal Server Error) para errores no controlados.
        }
    }

    public function show($id): JsonResponse
    {
        // Se mantiene el try-catch original para show, ya que el servicio no lanza InvalidArgumentException aquí.
        try {
            $venta = Venta::with('detalles.producto', 'cliente', 'usuario')->find($id); // Agregando relaciones para consistencia

            if (!$venta) {
                return response()->json(['message' => 'Venta no encontrada'], 404);
            }

            return response()->json([
                'message' => 'Venta obtenida correctamente',
                'data' => $venta
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener venta',
                'error' => $e->getMessage()
            ], 400); // Mantenemos 400 como en tu implementación anterior si el error es genérico.
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
        } catch (InvalidArgumentException $e) { // Captura excepciones de lógica de negocio del servicio
            return response()->json([
                'message' => 'Error en la actualización de la venta: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            // Log::error("Error inesperado en VentaController@update: " . $e->getMessage());
            return response()->json([
                'message' => 'Error inesperado al actualizar venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        // Se mantiene el try-catch original para destroy, ya que el servicio no lanza InvalidArgumentException aquí.
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
            ], 400); // Mantenemos 400 como en tu implementación anterior.
        }
    }
}