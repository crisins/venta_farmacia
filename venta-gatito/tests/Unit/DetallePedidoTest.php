<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DetallePedidoTest extends TestCase
{
    use RefreshDatabase;

    public function test_puede_crearse_un_detalle_pedido()
    {
        $detalle = DetallePedido::factory()->create();

        $this->assertDatabaseHas('detalle_pedido', [
            'id' => $detalle->id,
        ]);
    }

    public function test_detalle_pedido_tiene_relacion_con_pedido()
    {
        $pedido = Pedido::factory()->create();
        $detalle = DetallePedido::factory()->create(['pedido_id' => $pedido->id]);

        $this->assertEquals($pedido->id, $detalle->pedido->id);
    }

    public function test_detalle_pedido_tiene_relacion_con_producto()
    {
        $producto = Producto::factory()->create();
        $detalle = DetallePedido::factory()->create(['producto_id' => $producto->id]);

        $this->assertEquals($producto->id, $detalle->producto->id);
    }
}
