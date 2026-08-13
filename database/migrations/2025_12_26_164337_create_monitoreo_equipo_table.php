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
    Schema::create('monitoreo_equipo', function (Blueprint $table) {
        $table->id();
        $table->foreignId('monitoreo_id')->constrained('monitoreos')->onDelete('cascade');
        $table->foreignId('user_id')->constrained('users'); // El ID del usuario seleccionado
        $table->string('nombre_completo'); // Guardamos el nombre por si el usuario cambia luego
        $table->string('cargo')->default('Implementador');
        $table->string('institucion'); // DIRESA o MINSA
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoreo_equipo');
    }
};
