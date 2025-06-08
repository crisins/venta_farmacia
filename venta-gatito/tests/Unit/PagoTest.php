<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoTest extends TestCase
{
    public function testFillable()
    {
        $pago = new Pago();
        $this->assertEquals(
            ['pedido_id', 'metodo_pago', 'estado', 'fecha_pago'],
            $pago->getFillable()
        );
    }

    public function testCasts()
    {
        $pago = new Pago();
        $casts = $pago->getCasts();

        $this->assertArrayHasKey('fecha_pago', $casts);
        $this->assertEquals('datetime', $casts['fecha_pago']);
    }

    public function testPedidoRelation()
    {
        $pago = new Pago();
        $relation = $pago->pedido();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('pedido_id', $relation->getForeignKeyName());
    }
}
