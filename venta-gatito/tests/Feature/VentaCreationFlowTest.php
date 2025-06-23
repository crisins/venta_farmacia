<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VentaCreationFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_registrar_una_venta_con_multiples_productos_y_actualizar_stock_correctamente()
    {
        // 1. Crear precondiciones
        $usuario = Usuario::factory()->create(['tipo' => 'usuario']);
        $producto1 = Producto::factory()->create(['precio' => 1000, 'stock' => 10, 'requiere_receta' => false]);
        $producto2 = Producto::factory()->create(['precio' => 500, 'stock' => 15, 'requiere_receta' => false]);

        $requestData = [
            'usuario_id' => $usuario->id,
            'fecha' => '2025-06-21',
            'productos' => [
                ['producto_id' => $producto1->id, 'cantidad' => 3], // P1: 10 - 3 = 7
                ['producto_id' => $producto2->id, 'cantidad' => 5]  // P2: 15 - 5 = 10
            ]
        ];

        // 2. Ejecutar la acción (petición POST a la API)
        $response = $this->postJson('/api/ventas', $requestData);

        // 3. Verificar el resultado HTTP
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'data' => ['id', 'usuario_id', 'fecha', 'total', 'detalles']
                 ])
                 ->assertJsonFragment(['message' => 'Venta registrada correctamente']);

        // 4. Verificaciones de la base de datos para la venta principal
        $ventaId = $response->json('data.id');
        $this->assertDatabaseHas('ventas', [
            'id' => $ventaId,
            'usuario_id' => $usuario->id,
            'total' => (1000 * 3) + (500 * 5) // 3000 + 2500 = 5500
        ]);

        // 5. Verificaciones de la base de datos para los detalles de venta
        $this->assertDatabaseHas('detalle_ventas', [
            'venta_id' => $ventaId,
            'producto_id' => $producto1->id,
            'cantidad' => 3,
            'precio_unitario' => 1000,
            'subtotal' => 3000
        ]);
        $this->assertDatabaseHas('detalle_ventas', [
            'venta_id' => $ventaId,
            'producto_id' => $producto2->id,
            'cantidad' => 5,
            'precio_unitario' => 500,
            'subtotal' => 2500
        ]);

        // 6. Verificar stock actualizado en la base de datos
        $producto1->refresh();
        $producto2->refresh();
        $this->assertEquals(7, $producto1->stock);
        $this->assertEquals(10, $producto2->stock);
    }
}