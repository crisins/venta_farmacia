<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AtencionCliente;
use App\Models\Usuario; // Asegúrate de importar el modelo Usuario
use PHPUnit\Framework\Attributes\Test;

class AtencionClienteControllerTest extends TestCase
{
    #[Test]
    public function prueba_listar_todas_las_atenciones()
    {
        $response = $this->get('/api/atencion_cliente');
        
        $response->assertStatus(200);
    }

    #[Test]
    public function prueba_crear_una_atencion()
    {
        // Primero creamos un usuario falso con la factory
        $usuario = Usuario::factory()->create();

        $data = [
            'usuario_id' => $usuario->id,
            'tipo' => 'consulta',
            'detalle' => 'Necesito ayuda con mi pedido',
            'estado' => 'abierto',
            'fecha' => now()->toDateString(),
        ];

        $response = $this->post('/api/atencion_cliente', $data);

        $response->assertStatus(201);
    }
}
