<?php

namespace Database\Factories;

use App\Models\DetalleEgreso;
use App\Models\Egreso;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetalleEgresoFactory extends Factory
{
    protected $model = DetalleEgreso::class;

    public function definition(): array
    {
        $cantidad = $this->faker->numberBetween(1, 10);
        $precio = $this->faker->randomFloat(2, 100, 1000);

        return [
            'egreso_id' => Egreso::factory(),
            'producto_id' => \App\Models\Producto::factory(),
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal' => $cantidad * $precio,
        ];
    }
}

