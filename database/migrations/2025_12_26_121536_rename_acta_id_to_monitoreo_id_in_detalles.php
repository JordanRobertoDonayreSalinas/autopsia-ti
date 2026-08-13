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
    Schema::table('monitoreo_detalles', function (Blueprint $table) {
        // Primero eliminamos la clave foránea antigua
        $table->dropForeign(['acta_id']); 
        // Renombramos la columna
        $table->renameColumn('acta_id', 'monitoreo_id');
    });

    Schema::table('monitoreo_detalles', function (Blueprint $table) {
        // Creamos la nueva clave foránea hacia la tabla 'monitoreos'
        $table->foreign('monitoreo_id')->references('id')->on('monitoreos')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalles', function (Blueprint $table) {
            //
        });
    }
};
