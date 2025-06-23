<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Usuario;
use App\Models\Producto;

class VentaCreacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_venta_con_datos_validos()
    {
        // 1. Crear datos necesarios
        $usuario = Usuario::factory()->administrador()->create();

        // Crear un producto que explícitamente NO requiere receta para este test de datos válidos.
        $producto = Producto::factory()->noRequiereReceta()->create(['stock' => 10]);

        // 2. Armar los datos de la venta
        $ventaData = [
            'usuario_id' => $usuario->id,
            'fecha' => now()->toDateString(),
            'productos' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 3
                ]
            ]
        ];

        // 3. Enviar la petición POST
        $response = $this->postJson('/api/ventas', $ventaData);
        dump($response->json()); // Muestra la respuesta en la consola del test

        // 4. Verificar respuesta
        $response->assertStatus(201); // Ahora este assert debería pasar
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'usuario_id',
                'total',
                'fecha',
                'detalles' => [
                    [
                        'producto_id',
                        'cantidad',
                        'precio_unitario',
                        'subtotal'
                    ]
                ]
            ]
        ]);

        // 5. Verificar que el stock se haya descontado correctamente
        $producto->refresh();
        $this->assertEquals(7, $producto->stock);
    }
}