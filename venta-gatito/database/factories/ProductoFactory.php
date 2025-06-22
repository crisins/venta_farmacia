<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Producto; // Asegúrate de importar el modelo Producto

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
            'requiere_receta' => false, // Por defecto, los productos NO requieren receta
            'estado' => 'activo',
            'fecha_alta' => now(),
        ];
    }

    /**
     * Define un estado donde el producto NO requiere receta médica.
     * Útil para tests donde no queremos la validación de receta.
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
     * Define un estado donde el producto SÍ requiere receta médica.
     * Útil para tests específicos de validación de receta.
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