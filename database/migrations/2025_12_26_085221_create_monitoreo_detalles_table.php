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
        Schema::create('monitoreo_detalles', function (Blueprint $table) {
            $table->id();
            // Relación con la tabla actas
            $table->foreignId('acta_id')->constrained('actas')->onDelete('cascade');
            $table->string('modulo_nombre'); // Ejemplo: 'consultorios'
            $table->json('contenido'); // Guardará los datos de la imagen (turnos, cupos, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoreo_detalles');
    }
};