<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UsuarioFactory extends Factory
{
    public function definition()
    {
        return [
            'nombre' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'tipo' => $this->faker->randomElement(['administrador', 'usuario']),
            'telefono' => $this->faker->phoneNumber(),
            'direccion' => $this->faker->address(),
            'password' => bcrypt('password'),
        ];
    }

    public function administrador()
    {
        return $this->state(function (array $attributes) {
            return [
                'tipo' => 'administrador',
            ];
        });
    }
}