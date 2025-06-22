<?php

namespace Database\Factories;

use App\Models\Venta;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class VentaFactory extends Factory
{
    protected $model = Venta::class;

    public function definition(): array
    {
        return [
            'usuario_id' => Usuario::factory(), // El comprador es siempre un usuario
            'fecha' => now(),
            'total' => 0,
            // ...otros campos 
        ];
    }
}
