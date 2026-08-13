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
        Schema::create('mon_modulo_parto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('monitoreo_id');

            // Paso 1: Responsable
            $table->string('nombre_consultorio')->nullable();
            $table->string('personal_tipo_doc')->nullable();
            $table->string('personal_dni')->nullable();
            $table->string('personal_especialidad')->nullable();
            $table->string('personal_nombre')->nullable();
            $table->string('capacitacion_recibida')->nullable();
            $table->json('capacitacion_entes')->nullable(); // Guardará MINSA, DIRIS, etc.
            $table->string('capacitacion_otros_detalle')->nullable();

            // Paso 2: Materiales y Equipos
            $table->json('insumos_disponibles')->nullable(); // Aquí guardamos los Materiales
            $table->string('materiales_otros')->nullable();
            $table->json('equipos_listado')->nullable(); // Tabla dinámica de equipos
            $table->text('equipos_observaciones')->nullable();

            // Paso 3: Datos de Gestión
            $table->integer('nro_consultorios')->nullable();
            $table->integer('nro_gestantes_mes')->nullable();
            $table->string('gestion_hisminsa')->nullable();
            $table->string('gestion_reportes')->nullable();
            $table->string('gestion_reportes_socializa')->nullable();

            // Paso 4: Evidencias y Paso 5: Firma
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
        Schema::dropIfExists('mon_modulo_parto');
    }
};
