<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Cliente;
use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Inventario;

class VentaCreacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_venta_con_datos_validos()
    {
        // 1. Crear datos necesarios
        $cliente = Cliente::factory()->create();
        $usuario = Usuario::factory()->administrador()->create();

        // Crear inventario y producto asociado
        $inventario = Inventario::factory()->create([
            'stock_actual' => 10
        ]);
        $producto = $inventario->producto;

        // 2. Armar los datos de la venta
        $ventaData = [
            'cliente_id' => $cliente->id,
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
        dump($response->json());

        // 4. Verificar respuesta
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'cliente',
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
        $this->assertDatabaseHas('inventarios', [
            'producto_id' => $producto->id,
            'stock_actual' => 7
        ]);
    }
}
