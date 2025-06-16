<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Producto;
use App\Services\ProductoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_producto_se_elimina()
    {
        $producto = Producto::factory()->create();

        $service = new ProductoService();
        $service->eliminarProducto($producto->id);

        $this->assertDatabaseMissing('productos', [
            'id' => $producto->id,
        ]);

        $this->assertNull(Producto::find($producto->id));
    }
}
