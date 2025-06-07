<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AtencionCliente;
use App\Models\Usuario;

class AtencionClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuarios = Usuario::all();

        foreach ($usuarios as $usuario) {
            AtencionCliente::factory()->create([
                'usuario_id' => $usuario->id,
            ]);
        }
    }
}
