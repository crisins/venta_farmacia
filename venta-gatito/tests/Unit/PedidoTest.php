<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoTest extends TestCase
{
    public function testFillable()
    {
        $pedido = new Pedido();
        $this->assertEquals(
            ['usuario_id', 'fecha_pedido', 'estado', 'total'],
            $pedido->getFillable()
        );
    }

    public function testUsuarioRelation()
    {
        $pedido = new Pedido();
        $relation = $pedido->usuario();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('usuario_id', $relation->getForeignKeyName());
    }
}
