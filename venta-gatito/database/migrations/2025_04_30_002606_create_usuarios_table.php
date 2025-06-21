<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuariosTable extends Migration
{
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Cambiado de 'name'
            $table->string('email')->unique();
            $table->string('password');
            $table->string('tipo'); // Cambiado de 'rol' para coincidir con validaciones
            $table->string('telefono'); // Nuevo campo
            $table->string('direccion'); // Nuevo campo
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('usuarios');
    }
}

