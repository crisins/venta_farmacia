<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DetallePedidoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pedido_id' => null,
            'producto_id' => null,
            'cantidad' => 0,
            'precio_unit' => 0,
        ];
    }
}

