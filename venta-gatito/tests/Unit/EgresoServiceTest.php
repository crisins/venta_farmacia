<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Producto;
use App\Models\Inventario;
use App\Models\Proveedor;
use App\Models\Usuario;
use App\Services\EgresoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

class EgresoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_egreso_actualiza_stock_y_total()
    {
        $producto = Producto::factory()->create();
        $proveedor = Proveedor::factory()->create();
        $usuario = Usuario::factory()->create();

        Inventario::factory()->create([
            'producto_id' => $producto->id,
            'stock_actual' => 0
        ]);

        $data = [
            'proveedor_id' => $proveedor->id,
            'usuario_id' => $usuario->id,
            'fecha' => now()->toDateString(),
            'productos' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 5,
                    'precio_unitario' => 1000
                ]
            ]
        ];

        $service = new EgresoService();
        $egreso = $service->registrarEgreso($data);

        $inventario = Inventario::where('producto_id', $producto->id)->first();

        $this->assertEquals(5000, $egreso->total);
        $this->assertEquals(5, $inventario->stock_actual);
    }

    public function test_egreso_rechaza_cantidad_invalida()
    {
        $this->expectException(ValidationException::class);

        $producto = Producto::factory()->create();
        $proveedor = Proveedor::factory()->create();
        $usuario = Usuario::factory()->create();

        Inventario::factory()->create([
            'producto_id' => $producto->id,
            'stock_actual' => 0
        ]);

        $data = [
            'proveedor_id' => $proveedor->id,
            'usuario_id' => $usuario->id,
            'fecha' => now()->toDateString(),
            'productos' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => -3,  // cantidad inválida
                    'precio_unitario' => 1000
                ]
            ]
        ];

        $service = new EgresoService();
        $service->registrarEgreso($data);
    }
}

