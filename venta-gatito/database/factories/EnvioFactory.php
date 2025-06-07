<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EnvioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pedido_id' => \App\Models\Pedido::factory(),
            'empresa_log_id' => \App\Models\EmpresaLogistica::factory(),
            'estado_envio' => $this->faker->randomElement(['pendiente', 'en camino', 'entregado']),
            'fecha_envio' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'fecha_entrega' => $this->faker->dateTimeBetween('-15 days', 'now'),
        ];
    }
}
