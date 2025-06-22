<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Producto;

class ProductoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_expone_requiere_receta_y_campos_clave()
    {
        $producto = Producto::factory()->create([
            'requiere_receta' => true,
            'stock' => 15,
            'precio' => 123.45,
        ]);

        $response = $this->getJson('/api/productos');
        $response->assertStatus(200);
        $responseData = $response->json();
        $this->assertTrue(collect($responseData)->contains(function ($item) use ($producto) {
            return $item['id'] === $producto->id
                && $item['nombre'] === $producto->nombre
                && $item['descripcion'] === $producto->descripcion
                && (string)$item['precio'] === (string)$producto->precio
                && $item['stock'] == $producto->stock
                && $item['requiere_receta'] == true
                && $item['estado'] === $producto->estado
                && strpos($item['fecha_alta'], substr($producto->fecha_alta, 0, 10)) === 0;
        }), 'El producto esperado no se encontró en la respuesta.');
    }

    public function test_show_expone_requiere_receta_y_campos_clave()
    {
        $producto = Producto::factory()->create([
            'requiere_receta' => false,
            'stock' => 8,
            'precio' => 99.99,
        ]);

        $response = $this->getJson('/api/productos/' . $producto->id);
        $response->assertStatus(200)
            ->assertJson([
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio' => $producto->precio,
                'stock' => $producto->stock,
                'requiere_receta' => false,
                'estado' => $producto->estado,
                'fecha_alta' => $producto->fecha_alta,
            ]);
    }
}
