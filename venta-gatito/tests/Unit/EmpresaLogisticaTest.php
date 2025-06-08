<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\EmpresaLogistica;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmpresaLogisticaTest extends TestCase
{
    use RefreshDatabase;

    public function test_empresa_logistica_se_crea_correctamente()
    {
        $empresa = EmpresaLogistica::factory()->create([
            'nombre' => 'Logística Sur',
            'contacto' => 'Juan Pérez',
            'telefono' => '123456789',
            'email' => 'contacto@logisticasur.com',
        ]);

        $this->assertDatabaseHas('empresas_logisticas', [
            'nombre' => 'Logística Sur',
            'email' => 'contacto@logisticasur.com',
        ]);
    }

    public function test_los_campos_ocultos_no_aparecen_en_toarray()
    {
        $empresa = EmpresaLogistica::factory()->create();
        $array = $empresa->toArray();

        $this->assertArrayNotHasKey('created_at', $array);
        $this->assertArrayNotHasKey('updated_at', $array);
    }
}
