<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoProveedorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'producto_id' => \App\Models\Producto::factory(),
            'proveedor_id' => \App\Models\Proveedor::factory(),
            'precio_compra' => $this->faker->randomFloat(2, 1000, 5000),
            'stock_disponible' => $this->faker->numberBetween(10, 50),
            'tiempo_entrega_dias' => $this->faker->numberBetween(1, 10),
        ];
    }
}
