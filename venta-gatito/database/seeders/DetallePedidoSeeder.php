<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;

class DetallePedidoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pedidos = Pedido::all();
        $productos = Producto::all();

        foreach ($pedidos as $pedido) {
            DetallePedido::factory()->count(2)->create([
                'pedido_id' => $pedido->id,
                'producto_id' => $productos->random()->id,
            ]);
        }
    }
}
