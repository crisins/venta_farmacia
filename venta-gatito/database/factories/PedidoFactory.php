<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PedidoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'usuario_id' => \App\Models\Usuario::factory(),
            'fecha_pedido' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'estado' => $this->faker->randomElement(['pendiente', 'pagado', 'enviado']),
            'total' => $this->faker->randomFloat(2, 10000, 50000),
        ];
    }
}
