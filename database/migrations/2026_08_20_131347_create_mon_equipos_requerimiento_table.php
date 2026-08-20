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
        Schema::create('mon_equipos_requerimiento', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cabecera_monitoreo_id')
                  ->constrained('mon_cabecera_monitoreo')
                  ->onDelete('cascade');

            $table->string('modulo'); // Slug del consultorio, igual criterio que mon_equipos_computo
            $table->string('descripcion'); // Tipo de equipo requerido (CPU, LAPTOP, MONITOR, etc.)
            $table->integer('cantidad')->default(1);
            $table->text('observacion')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mon_equipos_requerimiento');
    }
};
