<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DetallePedidoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pedido_id' => \App\Models\Pedido::factory(),
            'producto_id' => \App\Models\Producto::factory(),
            'cantidad' => $this->faker->numberBetween(1, 5),
            'precio_unit' => $this->faker->randomFloat(2, 1000, 5000),
        ];
    }
}

