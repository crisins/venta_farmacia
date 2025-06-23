<?php

namespace Database\Factories;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovimientoInventarioFactory extends Factory
{
    protected $model = MovimientoInventario::class;

    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'tipo' => $this->faker->randomElement(['entrada', 'salida']),
            'cantidad' => $this->faker->numberBetween(1, 20),
            'descripcion' => $this->faker->sentence(),
            'fecha' => $this->faker->date(),
        ];
    }
}
