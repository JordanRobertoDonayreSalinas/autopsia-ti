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
        // 1. Modificar tabla ATENCIÓN PRENATAL
        Schema::table('mon_modulo_prenatal', function (Blueprint $table) {
            $table->string('dificultad_comunica_a')->nullable()->after('gestion_reportes_socializa');
            $table->string('dificultad_medio_uso')->nullable()->after('dificultad_comunica_a');
        });

        // 2. Modificar tabla PARTO (Si tiene la misma estructura)
        Schema::table('mon_modulo_parto', function (Blueprint $table) {
            $table->string('dificultad_comunica_a')->nullable()->after('gestion_reportes_socializa');
            $table->string('dificultad_medio_uso')->nullable()->after('dificultad_comunica_a');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir tabla ATENCIÓN PRENATAL
        Schema::table('mon_modulo_prenatal', function (Blueprint $table) {
            $table->dropColumn(['dificultad_comunica_a', 'dificultad_medio_uso']);
        });

        // Revertir tabla PARTO
        Schema::table('mon_modulo_parto', function (Blueprint $table) {
            $table->dropColumn(['dificultad_comunica_a', 'dificultad_medio_uso']);
        });
    }
};
