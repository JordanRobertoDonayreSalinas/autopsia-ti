<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('mon_monitoreo_modulos', function (Blueprint $table) {
        $table->id();
        // Relación con la cabecera (Si tu tabla de cabecera tiene otro nombre, ajústalo aquí)
        $table->foreignId('cabecera_monitoreo_id')
              ->constrained('mon_cabecera_monitoreo')
              ->onDelete('cascade');
        
        $table->string('modulo_nombre'); // 'triaje', 'medicina', etc.
        $table->json('contenido');       // Aquí se guardan las respuestas del formulario
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mon_monitoreo_modulos');
    }
};
