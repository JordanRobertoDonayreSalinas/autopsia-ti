<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Renombramos de la tabla errónea a la correcta
        Schema::rename('equipos_computo', 'mon_equipos_computo');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En caso de rollback, vuelve al nombre anterior
        Schema::rename('mon_equipos_computo', 'equipos_computo');
    }
};