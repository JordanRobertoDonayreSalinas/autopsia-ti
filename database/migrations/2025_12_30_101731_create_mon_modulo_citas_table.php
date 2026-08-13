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
        Schema::create('mon_modulo_citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoreo_id')->constrained('mon_cabecera_monitoreo')->onDelete('cascade');

            // --- 1. PERSONAL (Columnas independientes) ---
            $table->string('personal_nombre')->nullable();
            $table->string('personal_dni', 15)->nullable();
            $table->string('personal_turno', 20)->nullable();
            $table->json('personal_roles')->nullable(); // JSON (Multiple select)

            $table->string('capacitacion_recibida', 2)->nullable(); // SI / NO
            $table->json('capacitacion_entes')->nullable(); // JSON (Multiple select)
            $table->string('capacitacion_otros_detalle')->nullable();

            // --- 2. LOGÍSTICA ---
            $table->json('insumos_disponibles')->nullable(); // JSON (Checkboxes)
            $table->json('equipos_listado')->nullable(); // JSON (Tabla dinámica)
            $table->text('equipos_observaciones')->nullable();

            // --- 3. GESTIÓN ---
            $table->integer('nro_ventanillas')->default(0);
            $table->json('produccion_listado')->nullable(); // JSON (Tabla dinámica)

            // Calidad (Columnas individuales)
            $table->string('calidad_tiempo_espera', 2)->nullable();
            $table->string('calidad_paciente_satisfecho', 2)->nullable();
            $table->string('calidad_usa_reportes', 2)->nullable();
            $table->string('calidad_socializa_con')->nullable();

            // Dificultades
            $table->string('dificultad_comunica_a')->nullable();
            $table->string('dificultad_medio_uso')->nullable();

            // --- 4. EVIDENCIAS Y FIRMA ---
            $table->json('fotos_evidencia')->nullable();
            $table->longText('firma_grafica')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mon_modulo_citas');
    }
};
