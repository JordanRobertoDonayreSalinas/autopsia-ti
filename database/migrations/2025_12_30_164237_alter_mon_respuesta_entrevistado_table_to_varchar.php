<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mon_respuesta_entrevistado', function (Blueprint $table) {
            // Cambiamos las columnas a string (VARCHAR) para aceptar texto
            $table->string('recibio_capacitacion', 2)->change(); // Para "SI" o "NO"
            $table->string('inst_que_lo_capacito', 50)->nullable()->change();
            $table->string('inst_a_quien_comunica', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mon_respuesta_entrevistado', function (Blueprint $table) {
            // Por si necesitas revertir, aunque esto podría dar error si ya hay texto
            $table->integer('recibio_capacitacion')->change();
            $table->integer('inst_que_lo_capacito')->nullable()->change();
            $table->integer('inst_a_quien_comunica')->nullable()->change();
        });
    }
};