<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EgresoVentaFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_puede_registrar_egreso_luego_venta_y_verificar_stock_y_totales()
    {
        $proveedor = Proveedor::factory()->create();
        $usuarioAdmin = Usuario::factory()->create(['tipo' => 'administrador']);
        $usuarioVenta = Usuario::factory()->create(['tipo' => 'usuario']);
        $producto = Producto::factory()->create(['precio' => 1000, 'requiere_receta' => false, 'stock' => 2]);

        // 2. Registrar Egreso para AUMENTAR stock
        $egresoData = [
            'proveedor_id' => $proveedor->id,
            'usuario_id' => $usuarioAdmin->id,
            'fecha' => '2025-06-20',
            'tipo' => 'entrada',
            'productos' => [
                ['producto_id' => $producto->id, 'cantidad' => 8, 'precio_unitario' => 500]
            ]
        ];
        $responseEgreso = $this->postJson('/api/egresos', $egresoData);
        $responseEgreso->assertStatus(201)
                       ->assertJsonFragment(['message' => 'Egreso registrado exitosamente.']);
        $producto->refresh();
        $this->assertEquals(10, $producto->stock); // 2 + 8 = 10
        $egresoTotalEsperado = 8 * 500; // 4000
        $this->assertDatabaseHas('egresos', ['total' => $egresoTotalEsperado]);
        $egresoId = $responseEgreso->json('data.id');
        $this->assertDatabaseHas('detalle_egresos', [
            'egreso_id' => $egresoId,
            'producto_id' => $producto->id,
            'cantidad' => 8,
            'precio_unitario' => 500,
            'subtotal' => 4000
        ]);
        // 3. Registrar Venta usando el stock aumentado
        $ventaData = [
            'usuario_id' => $usuarioVenta->id,
            'fecha' => '2025-06-21',
            'productos' => [
                ['producto_id' => $producto->id, 'cantidad' => 7]
            ]
        ];
        $responseVenta = $this->postJson('/api/ventas', $ventaData);
        $responseVenta->assertStatus(201)
                      ->assertJsonFragment(['message' => 'Venta registrada correctamente']);
        $producto->refresh();
        $this->assertEquals(3, $producto->stock); // 10 - 7 = 3
        $ventaTotalEsperado = 7 * $producto->precio;
        $this->assertDatabaseHas('ventas', ['total' => $ventaTotalEsperado]);
        $ventaId = $responseVenta->json('data.id');
        $this->assertDatabaseHas('detalle_ventas', [
            'venta_id' => $ventaId,
            'producto_id' => $producto->id,
            'cantidad' => 7,
            'precio_unitario' => 1000,
            'subtotal' => 7000
        ]);
    }

    /** @test */
    public function test_no_puede_registrar_venta_despues_de_egreso_salida_que_agota_stock()
    {
        // 1. Precondiciones
        $proveedor = Proveedor::factory()->create();
        $usuarioAdmin = Usuario::factory()->create(['tipo' => 'administrador']);
        $usuarioVenta = Usuario::factory()->create(['tipo' => 'usuario']);
        $producto = Producto::factory()->create(['precio' => 1000, 'stock' => 5]);

        // 2. Registrar egreso de salida que agota el stock
        $egresoSalidaData = [
            'proveedor_id' => $proveedor->id,
            'usuario_id' => $usuarioAdmin->id,
            'fecha' => '2025-06-20',
            'tipo' => 'salida',
            'productos' => [
                ['producto_id' => $producto->id, 'cantidad' => 5, 'precio_unitario' => 500]
            ]
        ];
        $responseEgresoSalida = $this->postJson('/api/egresos', $egresoSalidaData);
        $responseEgresoSalida->assertStatus(201)
                             ->assertJsonFragment(['message' => 'Egreso registrado exitosamente.']);
        $producto->refresh();
        $this->assertEquals(0, $producto->stock);

        // 3. Intentar registrar una venta sin stock
        $ventaData = [
            'usuario_id' => $usuarioVenta->id,
            'fecha' => '2025-06-21',
            'productos' => [
                ['producto_id' => $producto->id, 'cantidad' => 1]
            ]
        ];
        $responseVenta = $this->postJson('/api/ventas', $ventaData);
        $responseVenta->assertStatus(422);
        $responseVenta->assertJsonValidationErrors('productos.0.cantidad');
        $this->assertDatabaseCount('ventas', 0);
    }
}