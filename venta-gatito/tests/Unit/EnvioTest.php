<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Envio;
use App\Models\Pedido;
use App\Models\EmpresaLogistica;

class EnvioTest extends TestCase
{
    public function testFillableProperties()
    {
        $envio = new Envio();

        $this->assertEquals([
            'pedido_id',
            'empresa_log_id',
            'estado_envio',
            'fecha_envio',
            'fecha_entrega',
        ], $envio->getFillable());
    }

    public function testCasts()
    {
        $envio = new Envio();

        $casts = $envio->getCasts();
        $this->assertArrayHasKey('fecha_envio', $casts);
        $this->assertEquals('datetime', $casts['fecha_envio']);
        $this->assertArrayHasKey('fecha_entrega', $casts);
        $this->assertEquals('datetime', $casts['fecha_entrega']);
    }

    public function testPedidoRelationship()
    {
        $envio = new Envio();
        $relation = $envio->pedido();

        $this->assertEquals('pedido_id', $relation->getForeignKeyName());
        $this->assertEquals(Pedido::class, get_class($relation->getRelated()));
    }

    public function testEmpresaLogisticaRelationship()
    {
        $envio = new Envio();
        $relation = $envio->empresaLogistica();

        $this->assertEquals('empresa_log_id', $relation->getForeignKeyName());
        $this->assertEquals(EmpresaLogistica::class, get_class($relation->getRelated()));
    }
}
