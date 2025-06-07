<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id(); 
            $table->string('nombre', 100);
            $table->string('email', 100);
            $table->string('password', 255);
            $table->string('direccion', 255);
            $table->string('telefono', 20);
            $table->enum('tipo', ['cliente', 'administrador', 'inactivo', 'externo']);
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamps(); // created_at y updated_at
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};