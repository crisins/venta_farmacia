<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Producto; 

class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->word(),
            'descripcion' => $this->faker->sentence(),
            'precio' => $this->faker->randomFloat(2, 1000, 10000),
            'stock' => $this->faker->numberBetween(10, 100),
            'requiere_receta' => false, // Por defecto, los productos NO Requieren receta
            'estado' => 'activo',
            'fecha_alta' => now(),
        ];
    }

    /**
     * Definir un estado donde el producto no requiere receta médica.
     * para tests donde no quieren la validación de receta.
     */
    public function noRequiereReceta(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'requiere_receta' => false,
            ];
        });
    }

    /**
     * Definir un estado donde el producto SÍ requiere receta médica.
     * para tests específicos de validación de receta.
     */
    public function requiereReceta(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'requiere_receta' => true,
            ];
        });
    }
}