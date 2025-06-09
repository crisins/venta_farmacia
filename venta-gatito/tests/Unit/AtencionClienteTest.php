<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AtencionCliente;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AtencionClienteTest extends TestCase
{
    use RefreshDatabase;

    public function test_crea_una_atencion_cliente()
    {
        $atencion = AtencionCliente::factory()->create();

        $this->assertDatabaseHas('atencion_cliente', [
            'id' => $atencion->id,
        ]);
    }

    public function test_relacion_con_usuario()
    {
        $usuario = Usuario::factory()->create();
        $atencion = AtencionCliente::factory()->create([
            'usuario_id' => $usuario->id,
        ]);

        $this->assertEquals($usuario->id, $atencion->usuario_id);
    }

    public function test_cast_de_fecha()
    {
        $atencion = AtencionCliente::factory()->create([
            'fecha' => '2025-06-01 12:34:56',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $atencion->fecha);
        $this->assertEquals('2025-06-01 12:34:56', $atencion->fecha->format('Y-m-d H:i:s'));
    }
}
