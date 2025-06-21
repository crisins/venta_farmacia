<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Usuario;
use App\Models\Producto; // Necesario para algunas pruebas de VentaService
use App\Models\Inventario; // Necesario para algunas pruebas de VentaService
use App\Models\DetalleVenta; // Necesario para algunas pruebas de VentaService
use App\Services\VentaService; // <-- ¡Asegúrate de importar VentaService!
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB; // Para transacciones si tu servicio usa DB::transaction

class VentaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected VentaService $ventaService; // <-- 1. Declara la propiedad

    protected function setUp(): void
    {
        parent::setUp();
        $this->ventaService = new VentaService(); // <-- 2. Inicializa VentaService aquí
    }

    /** @test */
    public function total_se_calcula_correctamente_al_crear_venta()
    {
        // Crear cliente y usuario necesarios para la venta
        $cliente = Cliente::factory()->create();
        $usuario = Usuario::factory()->create();
        $producto1 = Producto::factory()->create(['precio' => 100]);
        $producto2 = Producto::factory()->create(['precio' => 200]);

        Inventario::factory()->create(['producto_id' => $producto1->id, 'stock_actual' => 10]);
        Inventario::factory()->create(['producto_id' => $producto2->id, 'stock_actual' => 10]);

        $data = [
            'cliente_id' => $cliente->id,
            'usuario_id' => $usuario->id,
            'fecha' => '2023-01-01',
            'productos' => [
                ['producto_id' => $producto1->id, 'cantidad' => 2], // 2 * 100 = 200
                ['producto_id' => $producto2->id, 'cantidad' => 3], // 3 * 200 = 600
            ],
        ];

        $venta = $this->ventaService->registrarVenta($data); // Ahora $this->ventaService está definido

        $this->assertNotNull($venta->id);
        $this->assertEquals(800, $venta->total); // 200 + 600 = 800
        $this->assertDatabaseHas('ventas', ['id' => $venta->id, 'total' => 800]);

        // Verificar el stock de los productos
        $this->assertEquals(8, Inventario::where('producto_id', $producto1->id)->first()->stock_actual);
        $this->assertEquals(7, Inventario::where('producto_id', $producto2->id)->first()->stock_actual);
    }

    /** @test */
    public function stock_se_revierte_correctamente_si_falla_la_venta()
    {
        $producto = Producto::factory()->create(['precio' => 100]);
        $inventario = Inventario::factory()->create(['producto_id' => $producto->id, 'stock_actual' => 10]);

        $cliente = Cliente::factory()->create();
        $usuario = Usuario::factory()->create();

        $data = [
            'cliente_id' => $cliente->id,
            'usuario_id' => $usuario->id,
            'fecha' => '2023-01-01',
            'productos' => [
                ['producto_id' => $producto->id, 'cantidad' => 4] // Cantidad a vender
            ]
        ];

        // Simular una falla después de la reducción de stock
        // Aquí debes modificar tu VentaService para que lance una excepción
        // en algún punto después de la reducción de stock pero antes del commit
        DB::beginTransaction(); // Iniciar transacción manualmente para simular rollback
        try {
            $venta = $this->ventaService->registrarVenta($data); // <-- Línea 97
            // Forzar un rollback para probar la reversión del stock
            DB::rollBack();
        } catch (\Exception $e) {
            DB::rollBack(); // Asegurar rollback si hubo excepción
        }

        // Verificar stock después de la supuesta falla (debería ser el stock inicial)
        $inventario->refresh();
        $this->assertEquals(10, $inventario->stock_actual); // Debería volver a 10
        $this->assertDatabaseMissing('ventas', ['id' => optional($venta)->id]); // La venta no debería existir
    }


    /** @test */
    public function eliminar_venta_revierte_stock_correctamente()
    {
        $producto = Producto::factory()->create(['precio' => 500]);
        $inventario = Inventario::factory()->create(['producto_id' => $producto->id, 'stock_actual' => 10]);

        $cliente = Cliente::factory()->create();
        $usuario = Usuario::factory()->create();

        // Registrar una venta para luego eliminarla
        $data = [
            'cliente_id' => $cliente->id,
            'usuario_id' => $usuario->id,
            'fecha' => '2023-01-01',
            'productos' => [
                ['producto_id' => $producto->id, 'cantidad' => 3]
            ]
        ];
        $venta = $this->ventaService->registrarVenta($data);

        // Verificar que el stock se redujo después de la venta
        $inventario->refresh();
        $this->assertEquals(7, $inventario->stock_actual); // 10 - 3 = 7

        // Eliminar la venta
        $resultado = $this->ventaService->eliminarVenta($venta->id);

        $this->assertTrue($resultado);
        $this->assertDatabaseMissing('ventas', ['id' => $venta->id]);
        $this->assertDatabaseMissing('detalle_ventas', ['venta_id' => $venta->id]);

        // Verificar que el stock se revirtió
        $inventario->refresh();
        $this->assertEquals(10, $inventario->stock_actual); // Debería volver a 10 (7 + 3)
    }


    /** @test */
    public function actualizar_venta_ajusta_stock_correctamente()
    {
        // Productos iniciales
        $producto1 = Producto::factory()->create(['precio' => 100]);
        $inventario1 = Inventario::factory()->create(['producto_id' => $producto1->id, 'stock_actual' => 10]);

        $producto2 = Producto::factory()->create(['precio' => 200]);
        $inventario2 = Inventario::factory()->create(['producto_id' => $producto2->id, 'stock_actual' => 5]);

        $cliente = Cliente::factory()->create();
        $usuario = Usuario::factory()->create();

        // 1. Crear una venta inicial
        $initialData = [
            'cliente_id' => $cliente->id,
            'usuario_id' => $usuario->id,
            'fecha' => '2023-01-01',
            'productos' => [
                ['producto_id' => $producto1->id, 'cantidad' => 2] // Compra 2 del producto1
            ]
        ];
        $venta = $this->ventaService->registrarVenta($initialData);

        // Verificar stock después de la venta inicial
        $inventario1->refresh();
        $this->assertEquals(8, $inventario1->stock_actual); // 10 - 2 = 8
        $this->assertEquals(5, $inventario2->stock_actual); // Sin cambios

        // 2. Actualizar la venta:
        //    - Cambiar cantidad de producto1 de 2 a 1.
        //    - Añadir 3 unidades del producto2.
        $updatedData = [
            'cliente_id' => $cliente->id, // No cambia
            'usuario_id' => $usuario->id, // No cambia
            'fecha' => '2023-01-02',
            'productos' => [
                ['producto_id' => $producto1->id, 'cantidad' => 1], // De 2 a 1: libera 1
                ['producto_id' => $producto2->id, 'cantidad' => 3], // De 0 a 3: consume 3
            ]
        ];

        $updatedVenta = $this->ventaService->actualizarVenta($venta->id, $updatedData);

        $this->assertNotNull($updatedVenta);
        $this->assertEquals($venta->id, $updatedVenta->id);

        // Verificar stock después de la actualización
        $inventario1->refresh(); // Stock inicial 10. Se vendieron 2 (8). Se corrigió a 1 (liberó 1) -> 9.
        $this->assertEquals(9, $inventario1->stock_actual);

        $inventario2->refresh(); // Stock inicial 5. Se vendieron 3 -> 2.
        $this->assertEquals(2, $inventario2->stock_actual);

        // Verificar los detalles de la venta actualizada
        $updatedVenta->load('detalles');
        $this->assertCount(2, $updatedVenta->detalles);

        $detalle1 = $updatedVenta->detalles->where('producto_id', $producto1->id)->first();
        $this->assertEquals(1, $detalle1->cantidad);
        $this->assertEquals(100, $detalle1->precio_unitario);

        $detalle2 = $updatedVenta->detalles->where('producto_id', $producto2->id)->first();
        $this->assertEquals(3, $detalle2->cantidad);
        $this->assertEquals(200, $detalle2->precio_unitario);

        // Verificar el total de la venta actualizada: (1 * 100) + (3 * 200) = 100 + 600 = 700
        $this->assertEquals(700, $updatedVenta->total);
    }
}