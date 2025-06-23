<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\DetalleVentaService;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VentaRecetaValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function no_puede_registrar_venta_de_producto_con_receta_sin_indicacion_en_request()
    {
        $usuario = Usuario::factory()->create();
        $producto = Producto::factory()->create([
            'precio' => 100,
            'requiere_receta' => true,
            'stock' => 10
        ]);
        $data = [
            'usuario_id' => $usuario->id,
            'fecha' => '2025-06-21',
            'productos' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 1
                ]
            ]
        ];
        $detalleVentaService = new DetalleVentaService();
        $ventaService = new VentaService($detalleVentaService);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El producto requiere receta médica.');
        $ventaService->registrarVenta($data);
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('detalle_ventas', 0);
        $producto->refresh();
        $this->assertEquals(10, $producto->stock);
    }

    /** @test */
    public function puede_registrar_venta_de_producto_con_receta_con_indicacion_en_request()
    {
        $usuario = Usuario::factory()->create();
        $producto = Producto::factory()->create([
            'precio' => 100,
            'requiere_receta' => true,
            'stock' => 10
        ]);
        $data = [
            'usuario_id' => $usuario->id,
            'fecha' => '2025-06-21',
            'productos' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 1,
                    'con_receta' => true
                ]
            ]
        ];
        $detalleVentaService = new DetalleVentaService();
        $ventaService = new VentaService($detalleVentaService);
        $venta = $ventaService->registrarVenta($data);
        $producto->refresh();
        $this->assertEquals(9, $producto->stock);
        $this->assertDatabaseHas('ventas', ['id' => $venta->id, 'total' => 100]);
        $this->assertDatabaseHas('detalle_ventas', [
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'precio_unitario' => 100,
            'subtotal' => 100
        ]);
    }
}