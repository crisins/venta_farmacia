<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Cliente;
use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Inventario;
use App\Models\Venta;
use App\Models\DetalleVenta;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VentaEliminationFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_eliminar_una_venta_y_revertir_stock_correctamente()
    {
        // 1. Crear precondiciones: productos, inventario, cliente, usuario
        $cliente = Cliente::factory()->create();
        $usuario = Usuario::factory()->create(['tipo' => 'usuario']);
        $producto1 = Producto::factory()->create(['precio' => 1000, 'requiere_receta' => false]);
        $inventario1 = Inventario::factory()->create(['producto_id' => $producto1->id, 'stock_actual' => 10]);
        $producto2 = Producto::factory()->create(['precio' => 500, 'requiere_receta' => false]);
        $inventario2 = Inventario::factory()->create(['producto_id' => $producto2->id, 'stock_actual' => 15]);

        // 2. Registrar una venta para tener algo que eliminar
        // Usamos el servicio para asegurar que el stock se descuente correctamente
        $ventaService = new \App\Services\VentaService();
        $initialVentaData = [
            'cliente_id' => $cliente->id,
            'usuario_id' => $usuario->id,
            'fecha' => '2025-06-21',
            'productos' => [
                ['producto_id' => $producto1->id, 'cantidad' => 3], // Se usarán 3 de P1
                ['producto_id' => $producto2->id, 'cantidad' => 5]  // Se usarán 5 de P2
            ]
        ];
        $venta = $ventaService->registrarVenta($initialVentaData);


        // Asegurar que el stock se descontó inicialmente
        $inventario1->refresh(); // Refrescar para obtener el stock actual después de la "venta"
        $inventario2->refresh();
        $this->assertEquals(7, $inventario1->stock_actual); // 10 - 3 = 7
        $this->assertEquals(10, $inventario2->stock_actual); // 15 - 5 = 10

        // 3. Ejecutar la acción de eliminación (DELETE a la API)
        $response = $this->deleteJson('/api/ventas/' . $venta->id);

        // 4. Verificar el resultado HTTP
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Venta eliminada exitosamente']);

        // 5. Verificaciones de la base de datos: venta y detalles desaparecidos
        $this->assertDatabaseMissing('ventas', ['id' => $venta->id]);
        $this->assertDatabaseMissing('detalle_ventas', ['venta_id' => $venta->id]);

        // 6. Verificar que el stock se revirtió
        $inventario1->refresh(); // Refrescar para obtener el stock después de la eliminación
        $inventario2->refresh();
        $this->assertEquals(10, $inventario1->stock_actual); // Debería volver a 10
        $this->assertEquals(15, $inventario2->stock_actual); // Debería volver a 15
    }

    /** @test */
    public function no_puede_eliminar_venta_inexistente()
    {
        $response = $this->deleteJson('/api/ventas/9999'); // ID que no existe

        $response->assertStatus(404)
                 ->assertJsonFragment(['message' => 'Venta no encontrada o no se pudo eliminar']);
    }
}