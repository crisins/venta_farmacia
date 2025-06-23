<?php

namespace Database\Factories;

use App\Models\Egreso;
use App\Models\Proveedor;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class EgresoFactory extends Factory
{
    protected $model = Egreso::class;

    public function definition(): array
    {
        return [
            'proveedor_id' => Proveedor::factory(),
            'usuario_id' => Usuario::factory(),
            'fecha' => $this->faker->date(),
            'total' => $this->faker->randomFloat(2, 1000, 5000),
        ];
    }
}

