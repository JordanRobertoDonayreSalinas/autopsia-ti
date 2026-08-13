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
        Schema::table('actas', function (Blueprint $table) {
            // 1. Añadimos user_id para saber quién creó el acta (Relación con la tabla users)
            // Usamos after('id') para mantener un orden lógico en la base de datos
            if (!Schema::hasColumn('actas', 'user_id')) {
                $table->foreignId('user_id')
                      ->nullable() 
                      ->after('id')
                      ->constrained('users')
                      ->onDelete('cascade');
            }
            
            // 2. Añadimos la columna 'tipo' para separar Asistencia Técnica de Monitoreo
            // Por defecto será 'asistencia' para no afectar tus 199 registros actuales
            if (!Schema::hasColumn('actas', 'tipo')) {
                $table->string('tipo')
                      ->default('asistencia')
                      ->after('implementador');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actas', function (Blueprint $table) {
            // Eliminamos la clave foránea y las columnas
            if (Schema::hasColumn('actas', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }

            if (Schema::hasColumn('actas', 'tipo')) {
                $table->dropColumn('tipo');
            }
        });
    }
};