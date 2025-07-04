<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\DetalleVentaService;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VentaIntegracionTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_de_venta_valida_actualiza_stock_y_total()
    {
        $producto = Producto::factory()->create(['precio' => 1000, 'stock' => 10, 'requiere_receta' => false]);
        $usuario = Usuario::factory()->create();
        $data = [
            'usuario_id' => $usuario->id,
            'fecha' => now()->toDateString(),
            'productos' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 2,
                    'precio_unitario' => 1000
                ]
            ]
        ];
        $detalleVentaService = new DetalleVentaService();
        $ventaService = new VentaService($detalleVentaService);
        $venta = $ventaService->registrarVenta($data);
        $producto->refresh();
        $this->assertEquals(8, $producto->stock); // 10 - 2 vendidos
        $this->assertEquals(2000, $venta->total); // 2 * 1000
        $this->assertCount(1, $venta->detalles); // 1 detalle creado
    }
}