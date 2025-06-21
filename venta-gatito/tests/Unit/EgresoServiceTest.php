<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Egreso;
use App\Models\Proveedor;
use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Inventario;
use App\Models\MovimientoInventario;
use App\Services\EgresoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Artisan; // Puedes dejar esta línea, no hace daño.

class EgresoServiceTest extends TestCase
{
    use RefreshDatabase; // ¡Mantén este trait! Es clave para las pruebas.

    // El método setUp() se ejecuta antes de cada test
    protected function setUp(): void
    {
        parent::setUp();

        // *** IMPORTANTE: Hemos eliminado Artisan::call('migrate:fresh'); aquí. ***
        // RefreshDatabase se encargará de migrar la base de datos limpia para cada test.
        // Llamar a migrate:fresh dentro de una transacción (que RefreshDatabase ya abre)
        // causaba el error "cannot VACUUM from within a transaction" en SQLite.
    }

    /** @test */
    public function egreso_se_registra_correctamente()
    {
        $proveedor = Proveedor::factory()->create();
        $usuario = Usuario::factory()->create();
        $producto = Producto::factory()->create(['precio' => 100]);
        Inventario::factory()->create(['producto_id' => $producto->id, 'stock_actual' => 5]);

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
                    'precio_unitario' => 50, // Precio/Costo para el egreso
                ],
            ],
        ];

        $egreso = $service->registrarEgreso($data);

        $this->assertDatabaseHas('egresos', [
            'id' => $egreso->id,
            'total' => 500, // 10 * 50
            'tipo' => 'entrada',
        ]);

        $this->assertDatabaseHas('detalle_egresos', [
            'egreso_id' => $egreso->id,
            'producto_id' => $producto->id,
            'cantidad' => 10,
            'precio_unitario' => 50,
            'subtotal' => 500,
        ]);

        $inventario = Inventario::where('producto_id', $producto->id)->first();
        $this->assertEquals(15, $inventario->stock_actual); // 5 inicial + 10 entrada

        $this->assertDatabaseHas('movimiento_inventarios', [
            'producto_id' => $producto->id,
            'tipo' => 'entrada',
            'cantidad' => 10,
            'descripcion' => 'Egreso ID ' . $egreso->id . ' (entrada)',
        ]);
    }

    /** @test */
    public function egreso_de_salida_reduce_stock_correctamente()
    {
        $proveedor = Proveedor::factory()->create();
        $usuario = Usuario::factory()->create();
        $producto = Producto::factory()->create(['precio' => 100]);
        Inventario::factory()->create(['producto_id' => $producto->id, 'stock_actual' => 10]);

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
                    'precio_unitario' => 50, // Precio/Costo para el egreso
                ],
            ],
        ];

        $egreso = $service->registrarEgreso($data);

        $this->assertDatabaseHas('egresos', [
            'id' => $egreso->id,
            'total' => 150, // 3 * 50
            'tipo' => 'salida',
        ]);

        $this->assertDatabaseHas('detalle_egresos', [
            'egreso_id' => $egreso->id,
            'producto_id' => $producto->id,
            'cantidad' => 3,
            'precio_unitario' => 50,
            'subtotal' => 150,
        ]);

        $inventario = Inventario::where('producto_id', $producto->id)->first();
        $this->assertEquals(7, $inventario->stock_actual); // 10 inicial - 3 salida

        $this->assertDatabaseHas('movimiento_inventarios', [
            'producto_id' => $producto->id,
            'tipo' => 'salida',
            'cantidad' => 3,
            'descripcion' => 'Egreso ID ' . $egreso->id . ' (salida)',
        ]);
    }

    /** @test */
    public function no_puede_registrar_egreso_con_stock_insuficiente_en_salida()
    {
        $proveedor = Proveedor::factory()->create();
        $usuario = Usuario::factory()->create();
        $producto = Producto::factory()->create(['precio' => 100]);
        Inventario::factory()->create(['producto_id' => $producto->id, 'stock_actual' => 2]); // Solo 2 en stock

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
                    'precio_unitario' => 50, // Aseguramos que este campo existe
                ],
            ],
        ];

        // Esperamos una ValidationException
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Stock insuficiente para la salida del producto ID ' . $producto->id . '. Stock disponible: 2');

        $service->registrarEgreso($data);

        // Asegurarse de que el stock no cambió
        $inventario = Inventario::where('producto_id', $producto->id)->first();
        $this->assertEquals(2, $inventario->stock_actual);
        // Asegurarse de que no se creó el egreso ni el detalle ni el movimiento
        $this->assertDatabaseCount('egresos', 0);
        $this->assertDatabaseCount('detalle_egresos', 0);
        $this->assertDatabaseCount('movimiento_inventarios', 0);
    }


    /** @test */
    public function no_puede_registrar_egreso_con_tipo_faltante()
    {
        $service = new EgresoService();
        $data = [
            'proveedor_id' => Proveedor::factory()->create()->id,
            'usuario_id' => Usuario::factory()->create()->id,
            'fecha' => '2023-01-01',
            // 'tipo' => 'entrada', // Falta el tipo a propósito
            'productos' => [
                [
                    'producto_id' => Producto::factory()->create()->id,
                    'cantidad' => 1,
                    'precio_unitario' => 10, // Aseguramos que este campo existe
                ],
            ],
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The tipo field is required.');
        $service->registrarEgreso($data);
    }
}