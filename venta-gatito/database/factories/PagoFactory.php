<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PagoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pedido_id' => \App\Models\Pedido::factory(),
            'metodo_pago' => $this->faker->randomElement(['WebPay', 'PayPal']), // ← sin tilde
            'estado' => $this->faker->randomElement(['pendiente', 'completado', 'fallido']),
            'fecha_pago' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}