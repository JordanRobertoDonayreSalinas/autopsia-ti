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
    Schema::create('monitoreos', function (Blueprint $table) {
        $table->id();
        // Relación con el usuario que crea el monitoreo
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        
        // Relación con el establecimiento
        $table->foreignId('establecimiento_id')->constrained('establecimientos')->onDelete('cascade');
        
        $table->date('fecha');
        $table->string('responsable');
        $table->string('tema')->default('Monitoreo de Servicios');
        $table->string('modalidad')->default('Presencial');
        $table->string('implementador');
        
        // Estado y archivos
        $table->boolean('firmado')->default(false);
        $table->string('firmado_pdf')->nullable();
        
        // Campos para las 5 imágenes de evidencia detectadas en tu modelo Acta
        $table->string('imagen1')->nullable();
        $table->string('imagen2')->nullable();
        $table->string('imagen3')->nullable();
        $table->string('imagen4')->nullable();
        $table->string('imagen5')->nullable();
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoreos');
    }
};
