<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Producto;
use App\Models\Inventario;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Usuario;
use App\Services\VentaService;

class VentaEliminacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_eliminar_venta_devuelve_stock_y_elimina_detalles()
    {
        // Crear usuario y cliente válidos según la migración
        $cliente = Cliente::factory()->create();
        $usuario = Usuario::factory()->create();

        // Crear producto con inventario inicial
        $producto = Producto::factory()->create(['precio' => 1000]);
        $inventario = Inventario::factory()->create([
            'producto_id' => $producto->id,
            'stock_actual' => 10,
        ]);

        // Crear venta con 2 unidades del producto
        $ventaService = app()->make(VentaService::class);
        $data = [
            'cliente_id' => $cliente->id,
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
        $inventario->refresh();
        $this->assertEquals(8, $inventario->stock_actual);

        // Ahora eliminar la venta
        $ventaService->eliminarVenta($venta->id);

        // Verificar que el stock se restauró
        $inventario->refresh();
        $this->assertEquals(10, $inventario->stock_actual);

        // Verificar que la venta ya no existe
        $this->assertNull(Venta::find($venta->id));
    }
}

