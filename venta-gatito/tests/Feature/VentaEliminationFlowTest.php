<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VentaEliminationFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_puede_eliminar_una_venta_y_revertir_stock_correctamente()
    {
        // Arrange: Crear usuario y productos
        $usuario = Usuario::factory()->create(['tipo' => 'usuario']);
        $producto1 = Producto::factory()->noRequiereReceta()->create(['stock' => 10]);
        $producto2 = Producto::factory()->noRequiereReceta()->create(['stock' => 15]);

        // Act: Registrar una venta
        $ventaService = app(\App\Services\VentaService::class);
        $initialVentaData = [
            'usuario_id' => $usuario->id,
            'fecha' => '2025-06-21',
            'productos' => [
                ['producto_id' => $producto1->id, 'cantidad' => 3],
                ['producto_id' => $producto2->id, 'cantidad' => 5]
            ]
        ];
        $venta = $ventaService->registrarVenta($initialVentaData);

        // Assert: Stock descontado
        $producto1->refresh();
        $producto2->refresh();
        $this->assertEquals(7, $producto1->stock);
        $this->assertEquals(10, $producto2->stock);

        // Act: Eliminar la venta
        $response = $this->deleteJson('/api/ventas/' . $venta->id);

        // Assert: Respuesta y base de datos
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Venta eliminada exitosamente']);
        $this->assertDatabaseMissing('ventas', ['id' => $venta->id]);
        $this->assertDatabaseMissing('detalle_ventas', ['venta_id' => $venta->id]);

        // Assert: Stock revertido
        $producto1->refresh();
        $producto2->refresh();
        $this->assertEquals(10, $producto1->stock);
        $this->assertEquals(15, $producto2->stock);
    }

    /** @test */
    public function test_no_puede_eliminar_venta_inexistente()
    {
        $response = $this->deleteJson('/api/ventas/9999');
        $response->assertStatus(404)
                 ->assertJsonFragment(['message' => 'Venta no encontrada o no se pudo eliminar']);
    }
}