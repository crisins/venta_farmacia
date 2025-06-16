<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'), // o usa Hash::make()
            'rol' => 'cliente', // valor por defecto si no se usa estado
        ];
    }

    public function cliente()
    {
        return $this->state(fn () => ['rol' => 'cliente']);
    }

    public function administrador()
    {
        return $this->state(fn () => ['rol' => 'administrador']);
    }
}

