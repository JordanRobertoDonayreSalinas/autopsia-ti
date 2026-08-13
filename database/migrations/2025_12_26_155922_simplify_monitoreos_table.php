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
    Schema::table('monitoreos', function (Blueprint $table) {
        $columnasAEliminar = [
            'tema', 
            'modalidad', 
            'imagen1', 
            'imagen2', 
            'imagen3', 
            'imagen4', 
            'imagen5'
        ];

        foreach ($columnasAEliminar as $columna) {
            if (Schema::hasColumn('monitoreos', $columna)) {
                $table->dropColumn($columna);
            }
        }
    });
}

public function down(): void
{
    Schema::table('monitoreos', function (Blueprint $table) {
        // En caso de revertir, recreamos las columnas básicas
        $table->string('tema')->nullable();
        $table->string('modalidad')->nullable();
        $table->string('imagen1')->nullable();
        $table->string('imagen2')->nullable();
        $table->string('imagen3')->nullable();
        $table->string('imagen4')->nullable();
        $table->string('imagen5')->nullable();
    });
}
};
