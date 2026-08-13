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
        // Desactivamos restricciones por si la tabla tiene llaves foráneas
        Schema::disableForeignKeyConstraints();
        
        Schema::dropIfExists('monitoreo_programacion');
        
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // En caso de rollback, recreamos la estructura básica
        Schema::create('monitoreo_programacion', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};