<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Venta;
use App\Models\Usuario;
use App\Models\Producto;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VentaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // RefreshDatabase se encarga de la migración y limpieza de la base de datos de pruebas.
    }

    /** @test */
    public function total_se_calcula_correctamente_al_crear_venta()
    {
        $usuario = Usuario::factory()->create();
        $producto1 = Producto::factory()->create(['precio' => 1000, 'requiere_receta' => false, 'stock' => 10]);
        $producto2 = Producto::factory()->create(['precio' => 500, 'requiere_receta' => false, 'stock' => 10]);

        $ventaService = new VentaService();

        $data = [
            'usuario_id' => $usuario->id,
            'fecha' => now()->toDateString(),
            'productos' => [
                [
                    'producto_id' => $producto1->id,
                    'cantidad' => 2,
                ],
                [
                    'producto_id' => $producto2->id,
                    'cantidad' => 3,
                ],
            ],
        ];

        $venta = $ventaService->registrarVenta($data);

        $this->assertEquals((2 * 1000) + (3 * 500), $venta->total);
        $this->assertEquals(3500, $venta->total);
        $producto1->refresh();
        $producto2->refresh();
        $this->assertEquals(8, $producto1->stock);
        $this->assertEquals(7, $producto2->stock);
        $this->assertCount(2, $venta->detalles);
    }

    /** @test */
    public function stock_se_revierte_correctamente_si_falla_la_venta()
    {
        $usuario = Usuario::factory()->create();
        $producto = Producto::factory()->create(['precio' => 1000, 'requiere_receta' => false, 'stock' => 5]);
        $ventaService = new VentaService();
        $data = [
            'usuario_id' => $usuario->id,
            'fecha' => now()->toDateString(),
            'productos' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 10,
                ],
            ],
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Stock insuficiente para el producto ID ' . $producto->id . '. Disponible: 5.');
        try {
            $ventaService->registrarVenta($data);
        } catch (\Exception $e) {
            $this->assertDatabaseCount('ventas', 0);
            $this->assertDatabaseCount('detalle_ventas', 0);
            $producto->refresh();
            $this->assertEquals(5, $producto->stock);
            throw $e;
        }
    }

    /** @test */
    public function eliminar_venta_revierte_stock_correctamente()
    {
        $usuario = Usuario::factory()->create();
        $producto = Producto::factory()->create(['precio' => 100, 'requiere_receta' => false, 'stock' => 10]);
        $ventaService = new VentaService();
        $dataVenta = [
            'usuario_id' => $usuario->id,
            'fecha' => now()->toDateString(),
            'productos' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 3,
                ],
            ],
        ];
        $venta = $ventaService->registrarVenta($dataVenta);
        $producto->refresh();
        $this->assertEquals(7, $producto->stock);
        $eliminado = $ventaService->eliminarVenta($venta->id);
        $this->assertTrue($eliminado);
        $this->assertDatabaseMissing('ventas', ['id' => $venta->id]);
        $this->assertDatabaseMissing('detalle_ventas', ['venta_id' => $venta->id]);
        $producto->refresh();
        $this->assertEquals(10, $producto->stock);
    }

    /** @test */
    public function actualizar_venta_ajusta_stock_correctamente()
    {
        $usuario = Usuario::factory()->create();
        $producto1 = Producto::factory()->create(['precio' => 100, 'requiere_receta' => false, 'stock' => 10]);
        $producto2 = Producto::factory()->create(['precio' => 200, 'requiere_receta' => false, 'stock' => 5]);
        $producto3 = Producto::factory()->create(['precio' => 50, 'requiere_receta' => false, 'stock' => 20]);
        $ventaService = new VentaService();
        $initialData = [
            'usuario_id' => $usuario->id,
            'fecha' => now()->toDateString(),
            'productos' => [
                ['producto_id' => $producto1->id, 'cantidad' => 2],
                ['producto_id' => $producto2->id, 'cantidad' => 1],
            ],
        ];
        $venta = $ventaService->registrarVenta($initialData);
        $this->assertEquals(8, Producto::find($producto1->id)->stock);
        $this->assertEquals(4, Producto::find($producto2->id)->stock);
        $updateData = [
            'usuario_id' => $usuario->id,
            'fecha' => now()->toDateString(),
            'productos' => [
                ['producto_id' => $producto1->id, 'cantidad' => 3],
                ['producto_id' => $producto3->id, 'cantidad' => 2],
            ],
        ];
        $updatedVenta = $ventaService->actualizarVenta($venta->id, $updateData);
        $this->assertNotNull($updatedVenta);
        $this->assertEquals($venta->id, $updatedVenta->id);
        $producto1->refresh();
        $this->assertEquals(7, $producto1->stock);
        $producto2->refresh();
        $this->assertEquals(5, $producto2->stock);
        $producto3->refresh();
        $this->assertEquals(18, $producto3->stock);
        $this->assertCount(2, $updatedVenta->detalles);
        $this->assertEquals(3, $updatedVenta->detalles->where('producto_id', $producto1->id)->first()->cantidad);
        $this->assertEquals(2, $updatedVenta->detalles->where('producto_id', $producto3->id)->first()->cantidad);
        $this->assertNull($updatedVenta->detalles->where('producto_id', $producto2->id)->first());
        $expectedTotal = (3 * $producto1->precio) + (2 * $producto3->precio);
        $this->assertEquals($expectedTotal, $updatedVenta->total);
    }

    /** @test */
    public function puede_registrar_venta_con_producto_que_requiere_receta_con_indicacion_de_receta()
    {
        $usuario = Usuario::factory()->create();
        $productoReceta = Producto::factory()->create([
            'precio' => 100,
            'requiere_receta' => true,
            'stock' => 10
        ]);
        $ventaService = new VentaService();
        $data = [
            'usuario_id' => $usuario->id,
            'fecha' => '2025-06-21',
            'productos' => [
                [
                    'producto_id' => $productoReceta->id,
                    'cantidad' => 1,
                    'con_receta' => true
                ]
            ]
        ];
        $venta = $ventaService->registrarVenta($data);
        $this->assertDatabaseHas('ventas', [
            'id' => $venta->id,
            'total' => 100
        ]);
        $this->assertDatabaseHas('detalle_ventas', [
            'venta_id' => $venta->id,
            'producto_id' => $productoReceta->id,
            'cantidad' => 1
        ]);
        $productoReceta->refresh();
        $this->assertEquals(9, $productoReceta->stock);
    }
}