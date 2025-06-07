<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Envio;
use App\Models\Pedido;
use App\Models\EmpresaLogistica;

class EnvioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pedidos = Pedido::all();
        $empresas = EmpresaLogistica::all();

        foreach ($pedidos as $pedido) {
            Envio::factory()->create([
                'pedido_id' => $pedido->id,
                'empresa_log_id' => $empresas->random()->id,
            ]);
        }
    }
}
