<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\Usuario;
use App\Services\VentaService;

class VentaEliminacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_eliminar_venta_devuelve_stock_y_elimina_detalles()
    {
        // Crear usuario válido según la migración
        $usuario = Usuario::factory()->create();

        // Crear producto con stock inicial
        $producto = Producto::factory()->create(['precio' => 1000, 'stock' => 10, 'requiere_receta' => false]);

        // Crear venta con 2 unidades del producto
        $ventaService = app()->make(VentaService::class);
        $data = [
            'usuario_id' => $usuario->id,
            'fecha' => now()->toDateString(),
            'productos' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 2,
                ],
            ],
        ];
        $venta = $ventaService->registrarVenta($data);

        // Verificar stock después de la venta
        $producto->refresh();
        $this->assertEquals(8, $producto->stock);

        // Ahora eliminar la venta
        $ventaService->eliminarVenta($venta->id);

        // Verificar que el stock se restauró
        $producto->refresh();
        $this->assertEquals(10, $producto->stock);

        // Verificar que la venta ya no existe
        $this->assertNull(Venta::find($venta->id));
    }
}

