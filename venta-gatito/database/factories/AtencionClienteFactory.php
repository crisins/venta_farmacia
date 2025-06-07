<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AtencionClienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'usuario_id' => \App\Models\Usuario::factory(),
            'tipo' => $this->faker->randomElement(['reclamo', 'consulta']),
            'detalle' => $this->faker->sentence(),
            'estado' => $this->faker->randomElement(['abierto', 'resuelto', 'cerrado']),
            'fecha' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
