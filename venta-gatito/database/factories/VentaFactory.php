<?php

namespace Database\Factories;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class VentaFactory extends Factory
{
    protected $model = Venta::class;

    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'usuario_id' => Usuario::factory(),
            'fecha' => now(),
            'total' => 0,
        ];
    }
}
