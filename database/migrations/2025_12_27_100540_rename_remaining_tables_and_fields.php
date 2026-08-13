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

        // Renombramos de la antigua a la nueva

        Schema::rename('monitoreo_equipo', 'mon_equipo_monitoreo');

    }



    /**

     * Revierte la migración.

     */

    public function down(): void

    {

        // IMPORTANTE: Si revertimos, debe volver al nombre original

        Schema::rename('mon_equipo_monitoreo', 'monitoreo_equipo');

    }
};
