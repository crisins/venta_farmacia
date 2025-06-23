<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; 

class UpdateUsuariosTable extends Migration
{
    public function up()
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // 1. Renombrar campos existentes
            if (Schema::hasColumn('usuarios', 'name')) {
                $table->renameColumn('name', 'nombre');
            }
            
            if (Schema::hasColumn('usuarios', 'rol')) {
                $table->renameColumn('rol', 'tipo');
            }

            // 2. Agregar nuevos campos
            if (!Schema::hasColumn('usuarios', 'telefono')) {
                $table->string('telefono')->nullable()->after('password');
            }

            if (!Schema::hasColumn('usuarios', 'direccion')) {
                $table->string('direccion')->nullable()->after('telefono');
            }
        });

        // 3. Actualización de datos para registros existentes (solo si hay datos)
        if (DB::table('usuarios')->exists()) {
            DB::table('usuarios')->update([
                'telefono' => '0000000000',
                'direccion' => 'Dirección no especificada'
            ]);
        }

        // 4. Hacer los campos no nulos
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('telefono')->nullable(false)->change();
            $table->string('direccion')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Revertir los cambios
            if (Schema::hasColumn('usuarios', 'nombre')) {
                $table->renameColumn('nombre', 'name');
            }
            
            if (Schema::hasColumn('usuarios', 'tipo')) {
                $table->renameColumn('tipo', 'rol');
            }

            $table->dropColumn(['telefono', 'direccion']);
        });
    }
}