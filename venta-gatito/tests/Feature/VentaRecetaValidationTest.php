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
        // 1. Crear un usuario y un producto que REQUIERA RECETA
        $usuario = Usuario::factory()->create();
        $productoReceta = Producto::factory()->create([
            'precio' => 100,
            'requiere_receta' => true, // <-- Este producto requiere receta
            'stock' => 10
        ]);

        // 2. Preparar los datos de la venta (sin incluir ninguna "validación de receta")
        $data = [
            'usuario_id' => $usuario->id,
            'fecha' => '2025-06-21',
            'productos' => [
                [
                    'producto_id' => $productoReceta->id,
                    'cantidad' => 1 // Intentamos vender 1 unidad sin 'con_receta: true'
                ]
            ]
        ];

        // 3. Usar el servicio de Venta para registrar la venta
        $detalleVentaService = new DetalleVentaService();
        $ventaService = new VentaService($detalleVentaService);

        // 4. Esperar una excepción por la falta de receta
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El producto requiere receta médica.');
        $ventaService->registrarVenta($data);

        // 5. Asegurarse de que no se creó la venta ni se modificó el stock
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('detalle_ventas', 0);
        $productoReceta->refresh();
        $this->assertEquals(10, $productoReceta->stock); // Stock no debería haber cambiado
    }

    /** @test */
    public function puede_registrar_venta_de_producto_con_receta_con_indicacion_en_request()
    {
        // 1. Crear un usuario y un producto que REQUIERA RECETA
        $usuario = Usuario::factory()->create();
        $productoReceta = Producto::factory()->create([
            'precio' => 100,
            'requiere_receta' => true, // <-- Este producto requiere receta
            'stock' => 10
        ]);

        // 2. Preparar los datos de la venta (con 'con_receta: true')
        $data = [
            'usuario_id' => $usuario->id,
            'fecha' => '2025-06-21',
            'productos' => [
                [
                    'producto_id' => $productoReceta->id,
                    'cantidad' => 1,
                    'con_receta' => true // <-- Indicamos que se adjunta la receta
                ]
            ]
        ];

        // 3. Usar el servicio de Venta para registrar la venta
        $detalleVentaService = new DetalleVentaService();
        $ventaService = new VentaService($detalleVentaService);
        $venta = $ventaService->registrarVenta($data);

        // 4. Asegurarse de que la venta se creó correctamente y el stock se redujo
        $productoReceta->refresh();
        $this->assertEquals(9, $productoReceta->stock); // 10 - 1 = 9
        $this->assertEquals(1, $venta->detalles->first()->cantidad);
    }
}