<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EmpresaLogisticaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->company(),
            'telefono' => $this->faker->phoneNumber(),
            'contacto' => $this->faker->address(),
            'email' => $this->faker->unique()->safeEmail()
        ];
    }
}
