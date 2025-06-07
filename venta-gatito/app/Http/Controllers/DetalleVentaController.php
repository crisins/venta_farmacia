<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\DetalleVentaRequest;
use App\Services\DetalleVentaService;
class DetalleVentaController extends Controller
{
    protected $detalleVentaService;

    public function __construct(DetalleVentaService $detalleVentaService)
    {
        $this->detalleVentaService = $detalleVentaService;
    }
    #POST
    public function store(DetalleVentaRequest $request): JsonResponse
    {
        try {
            $detalle = $this->detalleVentaService->crearDetalle($request->validated());

            return response()->json([
                'message' => 'Detalle de venta registrado correctamente',
                'data' => $detalle
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear detalle de venta',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    #GET
    public function index(): JsonResponse
    {
        $detalles = $this->detalleVentaService->obtenerTodos();

        return response()->json([
            'message' => 'Lista de detalles de venta',
            'data' => $detalles
        ]);
    }
    #GET ID
    public function show($id): JsonResponse
    {
        $detalle = $this->detalleVentaService->obtenerPorId($id);

        if (!$detalle) {
            return response()->json([
                'message' => 'Detalle de venta no encontrado.'
            ], 404);
        }

        return response()->json([
            'message' => 'Detalle de venta encontrado',
            'data' => $detalle
        ]);
    }
    #PUT
    public function update(DetalleVentaRequest $request, $id): JsonResponse
    {
        $detalle = $this->detalleVentaService->actualizarDetalle($id, $request->validated());

        if (!$detalle) {
            return response()->json([
                'message' => 'Detalle de venta no encontrado.'
            ], 404);
        }

        return response()->json([
            'message' => 'Detalle de venta actualizado correctamente',
            'data' => $detalle
        ]);
    }
    #DELETE
    public function destroy($id): JsonResponse
    {
        $eliminado = $this->detalleVentaService->eliminarDetalle($id);

        if (!$eliminado) {
            return response()->json([
                'message' => 'Detalle de venta no encontrado.'
            ], 404);
        }

        return response()->json([
            'message' => 'Detalle de venta eliminado correctamente.'
        ]);
    }
}
