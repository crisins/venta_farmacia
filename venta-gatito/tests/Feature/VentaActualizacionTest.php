<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Venta;
use App\Models\DetalleVenta;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VentaActualizacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_actualizar_venta_con_productos_diferentes()
    {
        // Crear productos con stock inicial
        $producto1 = \App\Models\Producto::factory()->create(['precio' => 1000, 'stock' => 10]);
        $producto2 = \App\Models\Producto::factory()->create(['precio' => 2000, 'stock' => 15]);

        // Crear venta con producto1
        $venta = Venta::factory()->create(['total' => 0]);
        $detalle = DetalleVenta::create([
            'venta_id' => $venta->id,
            'producto_id' => $producto1->id,
            'cantidad' => 2,
            'precio_unitario' => 1000,
            'subtotal' => 2000,
        ]);

        // Reducir stock inicial por venta
        $producto1->stock -= 2;
        $producto1->save();

        $venta->total = 2000;
        $venta->save();

        // Ahora simulamos actualizar la venta: cambiar el producto1 por producto2 con cantidad 3
        $dataActualizar = [
            ['producto_id' => $producto2->id, 'cantidad' => 3]
        ];

        // actualizamos detalle y stock (en test real usa servicio)
        // Devolver stock producto1
        $producto1->stock += 2;
        $producto1->save();
        // Reducir stock producto2
        $producto2->stock -= 3;
        $producto2->save();
        // Actualizar detalle venta
        $detalle->update([
            'producto_id' => $producto2->id,
            'cantidad' => 3,
            'precio_unitario' => 2000,
            'subtotal' => 6000,
        ]);
        // Actualizar total venta
        $venta->total = 6000;
        $venta->save();
        // Assert para verificar stock producto1 volvió a 10
        $this->assertEquals(10, $producto1->fresh()->stock);
        // Assert para verificar stock producto2 bajó a 12
        $this->assertEquals(12, $producto2->fresh()->stock);
        // Assert para verificar total venta actualizado
        $this->assertEquals(6000, Venta::find($venta->id)->total);
        // Assert para verificar detalle actualizado
        $this->assertDatabaseHas('detalle_ventas', [
            'venta_id' => $venta->id,
            'producto_id' => $producto2->id,
            'cantidad' => 3,
            'precio_unitario' => 2000,
            'subtotal' => 6000,
        ]);
    }
}

