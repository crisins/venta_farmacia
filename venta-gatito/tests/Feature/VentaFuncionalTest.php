<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VentaFuncionalTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function no_permite_venta_sin_stock()
    {
        // Crear un producto con stock limitado
        $producto = Producto::factory()->create([
            'stock' => 5, // stock bajo para forzar error
        ]);

        // Crear cliente y usuario válidos
        $cliente = Cliente::factory()->create();
        $usuario = Usuario::factory()->create();

        // Datos de la venta con cantidad mayor al stock disponible
        $datosVenta = [
            'cliente_id' => $cliente->id,
            'usuario_id' => $usuario->id,
            'fecha' => date('Y-m-d'),
            'productos' => [
                [
                    'producto_id' => $producto->id,
                    'cantidad' => 10, // cantidad mayor al stock disponible
                ]
            ]
        ];

        // Realizar petición POST para crear venta
        $response = $this->postJson('/api/ventas', $datosVenta);

        // Verificar que responde con error 422 (validación)
        $response->assertStatus(422);

        // Verificar que haya error de validación en 'productos.0.cantidad'
        $response->assertJsonValidationErrors('productos.0.cantidad');

        // Verificar que no se haya creado ninguna venta en la base de datos
        $this->assertDatabaseCount('ventas', 0);
    }
}
