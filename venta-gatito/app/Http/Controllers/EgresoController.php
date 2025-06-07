<?php

/* * EgresoService.php
 * Servicio para manejar la lógica de negocio relacionada con los egresos.
 * Este servicio se encarga de interactuar con el modelo Egreso y realizar operaciones CRUD.
 *
 * @package App\Services
 */

namespace App\Http\Controllers;

use App\Services\EgresoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class EgresoController extends Controller
{
    protected $egresoService;

    public function __construct(EgresoService $egresoService)
    {
        $this->egresoService = $egresoService;
    }

    /**
     * Mostrar un egreso específico.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $egreso = $this->egresoService->mostrarEgreso($id);

            return response()->json([
                'data' => $egreso,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al buscar egreso.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
    /**
     * Listar todos los egresos.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $egresos = $this->egresoService->listarEgresos(
                $request->input('proveedor_id'),
                $request->input('producto_id'),
                $request->input('fecha_inicio'),
                $request->input('fecha_fin'),
            );

            return response()->json([
                'data' => $egresos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al listar egresos.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Registrar un nuevo egreso.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $egreso = $this->egresoService->registrarEgreso($request->all());

            return response()->json([
                'message' => 'Egreso registrado exitosamente.',
                'data' => $egreso,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error inesperado.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Actualizar un egreso existente.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $egreso = $this->egresoService->actualizarEgreso($id, $request->all());

            return response()->json([
                'message' => 'Egreso actualizado correctamente.',
                'data' => $egreso,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar egreso.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Eliminar un egreso existente.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->egresoService->eliminarEgreso($id);

            return response()->json([
                'message' => 'Egreso eliminado correctamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar egreso.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Generar un reporte de egresos por fechas.
     */
    public function reporte(Request $request): JsonResponse
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        try {
            $egresos = $this->egresoService->reportePorFechas(
                $request->fecha_inicio,
                $request->fecha_fin
            );

            return response()->json([
                'message' => 'Reporte generado correctamente.',
                'data' => $egresos,
                'total_egresado' => $egresos->sum('total'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al generar el reporte.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
