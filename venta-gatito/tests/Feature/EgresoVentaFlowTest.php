<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Cliente;
use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Inventario;
use App\Models\Proveedor;
use App\Models\Egreso;
use App\Models\DetalleEgreso;
use App\Models\Venta;
use App\Models\DetalleVenta;

use Illuminate\Foundation\Testing\RefreshDatabase;

class EgresoVentaFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_registrar_egreso_luego_venta_y_verificar_stock_y_totales()
    {
        // 1. Precondiciones
        $proveedor = Proveedor::factory()->create();
        $usuarioAdmin = Usuario::factory()->create(['tipo' => 'administrador']);
        $usuarioVenta = Usuario::factory()->create(['tipo' => 'usuario']);
        $cliente = Cliente::factory()->create();

        $producto = Producto::factory()->create(['precio' => 1000, 'requiere_receta' => false]);
        // Stock inicial bajo para demostrar el egreso
        $inventario = Inventario::factory()->create(['producto_id' => $producto->id, 'stock_actual' => 2]);

        // 2. Registrar Egreso para AUMENTAR stock
        $egresoData = [
            'proveedor_id' => $proveedor->id,
            'usuario_id' => $usuarioAdmin->id,
            'fecha' => '2025-06-20',
            'tipo' => 'entrada',
            'productos' => [
                ['producto_id' => $producto->id, 'cantidad' => 8, 'precio_unitario' => 500] // Aumentar 8 unidades
            ]
        ];

        $responseEgreso = $this->postJson('/api/egresos', $egresoData);
        $responseEgreso->assertStatus(201)
                       ->assertJsonFragment(['message' => 'Egreso registrado exitosamente.']);

        $inventario->refresh();
        $this->assertEquals(10, $inventario->stock_actual); // Stock debería ser 2 (inicial) + 8 (egreso) = 10
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
            'cliente_id' => $cliente->id,
            'usuario_id' => $usuarioVenta->id,
            'fecha' => '2025-06-21',
            'productos' => [
                ['producto_id' => $producto->id, 'cantidad' => 7] // Vender 7 de 10
            ]
        ];

        $responseVenta = $this->postJson('/api/ventas', $ventaData);
        $responseVenta->assertStatus(201)
                      ->assertJsonFragment(['message' => 'Venta registrada correctamente']);

        // 4. Verificar estado final
        $inventario->refresh();
        $this->assertEquals(3, $inventario->stock_actual); // Stock debería ser 10 (después de egreso) - 7 (venta) = 3
        $ventaTotalEsperado = 7 * $producto->precio; // 7 * 1000 = 7000
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
    public function no_puede_registrar_venta_despues_de_egreso_salida_que_agota_stock()
    {
        // 1. Precondiciones
        $proveedor = Proveedor::factory()->create();
        $usuarioAdmin = Usuario::factory()->create(['tipo' => 'administrador']);
        $usuarioVenta = Usuario::factory()->create(['tipo' => 'usuario']);
        $cliente = Cliente::factory()->create();

        $producto = Producto::factory()->create(['precio' => 1000, 'requiere_receta' => false]);
        $inventario = Inventario::factory()->create(['producto_id' => $producto->id, 'stock_actual' => 5]);

        // 2. Registrar Egreso de SALIDA que deja stock insuficiente para la venta
        $egresoData = [
            'proveedor_id' => $proveedor->id,
            'usuario_id' => $usuarioAdmin->id,
            'fecha' => '2025-06-20',
            'tipo' => 'salida',
            'productos' => [
                ['producto_id' => $producto->id, 'cantidad' => 5, 'precio_unitario' => 100] // Saca todo el stock
            ]
        ];

        $responseEgreso = $this->postJson('/api/egresos', $egresoData);
        $responseEgreso->assertStatus(201); // Egreso de salida exitoso si el stock es suficiente

        $inventario->refresh();
        $this->assertEquals(0, $inventario->stock_actual); // Stock debería ser 5 - 5 = 0

        // 3. Intentar Registrar Venta con stock insuficiente
        $ventaData = [
            'cliente_id' => $cliente->id,
            'usuario_id' => $usuarioVenta->id,
            'fecha' => '2025-06-21',
            'productos' => [
                ['producto_id' => $producto->id, 'cantidad' => 1] // Intenta vender 1
            ]
        ];

        $responseVenta = $this->postJson('/api/ventas', $ventaData);
        $responseVenta->assertStatus(422)
                      ->assertJsonValidationErrors(['productos.0.cantidad'])
                      // <--- CORREGIDO AQUÍ: El mensaje debe coincidir exactamente
                      ->assertJsonFragment(['Stock insuficiente para el producto ID ' . $producto->id . '. Stock actual: 0, solicitado: 1.']); 
        // Asegurarse de que no se creó ninguna venta
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('detalle_ventas', 0);
    }
}