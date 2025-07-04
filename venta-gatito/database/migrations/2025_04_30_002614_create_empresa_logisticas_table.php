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
        Schema::create('empresas_logisticas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('contacto', 100);
            $table->string('telefono', 20);
            $table->string('email', 100);
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas_logisticas');
    }
};
