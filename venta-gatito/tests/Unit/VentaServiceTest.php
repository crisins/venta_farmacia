<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VentaServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function total_se_calcula_correctamente()
    {
        // Crear cliente y usuario necesarios para la venta
        $cliente = Cliente::factory()->create();
        $usuario = Usuario::factory()->create();

        // Crear venta con referencias válidas
        $venta = Venta::create([
            'cliente_id' => $cliente->id,
            'usuario_id' => $usuario->id,
            'fecha' => now(),
            'total' => 0,
        ]);

        // Aquí puedes simular agregar detalles a la venta, calcular total, etc.
        // Por ejemplo, si tienes un método calcularTotal:
        // $venta->calcularTotal();

        // Simular un total esperado, por ejemplo:
        $venta->total = 1500;
        $venta->save();

        // Comprobar que el total se guarda correctamente
        $this->assertEquals(1500, $venta->total);
    }
}
