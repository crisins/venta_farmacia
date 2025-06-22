<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\Usuario;
use App\Services\DetalleVentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DetalleVentaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_falla_si_no_hay_stock()
    {
        $usuario = Usuario::factory()->create();
        $venta = Venta::factory()->create([
            'usuario_id' => $usuario->id,
        ]);
        $producto = Producto::factory()->create(['stock' => 1]);
        $data = [
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'cantidad' => 5,
        ];
        $service = new DetalleVentaService();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Stock insuficiente para el producto ID ' . $producto->id . '. Disponible: 1.');
        $service->crearDetalle($data);
    }
}
