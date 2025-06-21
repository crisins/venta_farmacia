<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Egreso;
use App\Models\Proveedor;
use App\Models\Usuario;
use App\Models\Producto;
use App\Services\EgresoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Artisan;

class EgresoServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function egreso_se_registra_correctamente()
    {
        $proveedor = Proveedor::factory()->create();
        $usuario = Usuario::factory()->create();
        $producto = Producto::factory()->create(['precio' => 100, 'stock' => 5]);
        $service = new EgresoService();
        $data = [
            'proveedor_id' => $proveedor->id,
            'usuario_id' => $usuario->id,
            'fecha' => '2023-01-01',
            'tipo' => 'entrada',
            'productos' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 10,
                    'precio_unitario' => 50,
                ],
            ],
        ];
        $egreso = $service->registrarEgreso($data);
        $this->assertDatabaseHas('egresos', [
            'id' => $egreso->id,
            'total' => 500,
            'tipo' => 'entrada',
        ]);
        $this->assertDatabaseHas('detalle_egresos', [
            'egreso_id' => $egreso->id,
            'producto_id' => $producto->id,
            'cantidad' => 10,
            'precio_unitario' => 50,
            'subtotal' => 500,
        ]);
        $producto->refresh();
        $this->assertEquals(15, $producto->stock); // 5 inicial + 10 entrada
    }

    /** @test */
    public function egreso_de_salida_reduce_stock_correctamente()
    {
        $proveedor = Proveedor::factory()->create();
        $usuario = Usuario::factory()->create();
        $producto = Producto::factory()->create(['precio' => 100, 'stock' => 10]);
        $service = new EgresoService();
        $data = [
            'proveedor_id' => $proveedor->id,
            'usuario_id' => $usuario->id,
            'fecha' => '2023-01-02',
            'tipo' => 'salida',
            'productos' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 3,
                    'precio_unitario' => 50,
                ],
            ],
        ];
        $egreso = $service->registrarEgreso($data);
        $this->assertDatabaseHas('egresos', [
            'id' => $egreso->id,
            'total' => 150,
            'tipo' => 'salida',
        ]);
        $this->assertDatabaseHas('detalle_egresos', [
            'egreso_id' => $egreso->id,
            'producto_id' => $producto->id,
            'cantidad' => 3,
            'precio_unitario' => 50,
            'subtotal' => 150,
        ]);
        $producto->refresh();
        $this->assertEquals(7, $producto->stock); // 10 inicial - 3 salida
    }

    /** @test */
    public function no_puede_registrar_egreso_con_stock_insuficiente_en_salida()
    {
        $proveedor = Proveedor::factory()->create();
        $usuario = Usuario::factory()->create();
        $producto = Producto::factory()->create(['precio' => 100, 'stock' => 2]); // Solo 2 en stock
        $service = new EgresoService();
        $data = [
            'proveedor_id' => $proveedor->id,
            'usuario_id' => $usuario->id,
            'fecha' => '2023-01-03',
            'tipo' => 'salida',
            'productos' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 5, // Intenta sacar 5
                    'precio_unitario' => 50,
                ],
            ],
        ];
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Stock insuficiente para la salida del producto ID ' . $producto->id);
        $service->registrarEgreso($data);
        $producto->refresh();
        $this->assertEquals(2, $producto->stock); // Stock no cambió
    }
}