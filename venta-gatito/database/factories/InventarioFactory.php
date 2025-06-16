<?php

namespace Database\Factories;

use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventarioFactory extends Factory
{
    protected $model = Inventario::class;

    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'stock_actual' => $this->faker->numberBetween(0, 20),
        ];
    }
}
