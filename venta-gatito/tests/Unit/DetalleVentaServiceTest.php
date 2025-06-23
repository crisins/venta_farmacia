<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Producto;
use App\Models\Inventario;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Usuario;
use App\Services\DetalleVentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DetalleVentaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_falla_si_no_hay_stock()
    {
        // Crear cliente y usuario necesarios para la venta
        $cliente = Cliente::factory()->create();
        $usuario = Usuario::factory()->create();

        // Crear venta vinculada a cliente y usuario válidos
        $venta = Venta::factory()->create([
            'cliente_id' => $cliente->id,
            'usuario_id' => $usuario->id,
        ]);

        // Crear producto con precio definido
        $producto = Producto::factory()->create(['precio' => 1000]);

        // Crear inventario con stock insuficiente para la cantidad solicitada
        Inventario::factory()->create([
            'producto_id' => $producto->id,
            'stock_actual' => 1,
        ]);

        // Datos del detalle de venta con cantidad mayor al stock disponible
        $data = [
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'cantidad' => 5,
        ];

        $service = new DetalleVentaService();

        // Intentar crear detalle y esperar excepción por stock insuficiente
        try {
            $service->crearDetalle($data);
            $this->fail('La excepción por stock insuficiente no fue lanzada.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Stock insuficiente', $e->getMessage());
        }
    }
}
