<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipos_computo', function (Blueprint $table) {
            $table->id();
            
            // Relación con la cabecera (Tabla ya renombrada)
            $table->foreignId('cabecera_monitoreo_id')
                  ->constrained('mon_cabecera_monitoreo')
                  ->onDelete('cascade');

            $table->string('modulo'); // Ej: 'Ventanilla', 'Triaje', etc.
            $table->string('descripcion'); // Ej: 'Computadora I7', 'Impresora'
            $table->integer('cantidad')->default(1);
            
            // Estado: Podría ser 'Operativo', 'No Operativo', 'Regular'
            $table->string('estado')->default('Operativo');
            
            // Propio: Booleano (true = Propio, false = Prestado/Alquilado)
            $table->boolean('propio')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipos_computo');
    }
};