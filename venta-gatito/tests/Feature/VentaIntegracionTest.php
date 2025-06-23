<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Producto;
use App\Models\Inventario;
use App\Models\Usuario;
use App\Models\Cliente;
use App\Services\DetalleVentaService;
use App\Services\VentaService;

class VentaIntegracionTest extends TestCase
{
    public function test_registro_de_venta_valida_actualiza_stock_y_total()
    {
        $producto = Producto::factory()->create(['precio' => 1000]);

        Inventario::factory()->create([
            'producto_id' => $producto->id,
            'stock_actual' => 10
        ]);

        $usuario = Usuario::factory()->create();
        $cliente = Cliente::factory()->create();

        $data = [
            'cliente_id' => $cliente->id,
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

        $inventario = Inventario::where('producto_id', $producto->id)->first();

        $this->assertEquals(8, $inventario->stock_actual); // 10 - 2 vendidos
        $this->assertEquals(2000, $venta->total);          // 2 * 1000
        $this->assertCount(1, $venta->detalles);           // 1 detalle creado
    }
}
