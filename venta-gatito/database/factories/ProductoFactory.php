<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->word(),
            'descripcion' => $this->faker->sentence(),
            'precio' => $this->faker->randomFloat(2, 1000, 10000),
            'stock' => $this->faker->numberBetween(10, 100),
            'requiere_receta' => $this->faker->boolean(), // Aquí asignas un valor booleano
            'estado' => 'activo',
            'fecha_alta' => now(),
        ];
    }
}
