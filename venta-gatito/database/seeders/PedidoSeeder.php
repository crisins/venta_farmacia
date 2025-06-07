<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pedido;
use App\Models\Usuario;

class PedidoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuarios = Usuario::all();

        foreach ($usuarios as $usuario) {
            Pedido::factory()->count(1)->create([
                'usuario_id' => $usuario->id,
            ]);
        }
    }
}
