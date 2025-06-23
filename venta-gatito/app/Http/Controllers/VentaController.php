<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Services\VentaService;
use App\Http\Requests\VentaRequest;
use App\Models\Venta;


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

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar venta',
                'error' => $e->getMessage()
            ], 400);
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
        $venta = $this->ventaService->actualizarVenta($id, $request->validated());

        if (!$venta) {
            return response()->json(['message' => 'Venta no encontrada.'], 404);
        }

        return response()->json([
            'message' => 'Venta actualizada correctamente',
            'data' => $venta
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $eliminada = $this->ventaService->eliminarVenta($id);

        if (!$eliminada) {
            return response()->json(['message' => 'Venta no encontrada.'], 404);
        }

        return response()->json(['message' => 'Venta eliminada correctamente.']);
    }
}
