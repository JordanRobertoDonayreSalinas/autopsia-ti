<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Eliminamos la llave primaria actual (doc)
        Schema::table('mon_equipo_monitoreo', function (Blueprint $table) {
            try {
                // En MySQL debemos usar este comando directo para soltar la primaria
                DB::statement('ALTER TABLE mon_equipo_monitoreo DROP PRIMARY KEY');
            } catch (\Exception $e) {
                // Si no existe primaria, continuamos
            }
        });

        // 2. Agregamos el ID autoincrementable como nueva llave primaria
        Schema::table('mon_equipo_monitoreo', function (Blueprint $table) {
            $table->id()->first();
            
            // Opcional: Aseguramos que el doc sea indexado para búsquedas rápidas, 
            // pero que permita valores repetidos (participación en múltiples actas)
            $table->string('doc')->change(); 
        });
    }

    public function down(): void
    {
        Schema::table('mon_equipo_monitoreo', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->primary('doc');
        });
    }
};