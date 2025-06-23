<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RecetaMedicaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'usuario_id' => \App\Models\Usuario::factory(),
            'archivo_url' => $this->faker->url(),
            'fecha_subida' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'estado_validacion' => $this->faker->randomElement(['pendiente', 'aprobada', 'rechazada']),
        ];
    }
}