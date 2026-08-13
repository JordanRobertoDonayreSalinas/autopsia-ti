<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mon_detalle_modulos', function (Blueprint $table) {
            $table->id();
            // Relación con la cabecera
            $table->bigInteger('cabecera_monitoreo_id')->unsigned();
            
            $table->string('modulo_nombre', 50);
            $table->string('personal_nombre', 60)->nullable();
            $table->string('personal_dni', 15)->nullable();
            $table->string('personal_turno', 50)->nullable();
            
            // Usamos longText para coincidir con tu estructura
            $table->longText('personal_roles');
            $table->longText('contenido')->nullable(); 
            $table->longText('firma_digital')->nullable();
            
            $table->string('foto_1', 255)->nullable();
            $table->string('foto_2', 255)->nullable();
            $table->string('pdf_firmado_path', 255)->nullable();
            
            // Campo de timestamp manual y los de Laravel
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamps();

            // Definición de llave foránea (opcional pero recomendada)
            $table->foreign('cabecera_monitoreo_id')
                  ->references('id')->on('mon_cabecera_monitoreo')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mon_detalle_modulos');
    }
};