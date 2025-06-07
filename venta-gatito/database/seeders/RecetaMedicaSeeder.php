<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RecetaMedica;
use App\Models\Usuario;

class RecetaMedicaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuarios = Usuario::all();

        foreach ($usuarios as $usuario) {
            RecetaMedica::factory()->count(1)->create([
                'usuario_id' => $usuario->id,
            ]);
        }
    }
}
