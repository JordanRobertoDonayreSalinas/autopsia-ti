<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Desactivamos por completo las llaves foráneas para evitar bloqueos
        Schema::disableForeignKeyConstraints();

        // 2. Agregamos las columnas necesarias si no existen
        Schema::table('mon_equipo_monitoreo', function (Blueprint $table) {
            if (!Schema::hasColumn('mon_equipo_monitoreo', 'tipo_doc')) {
                $table->string('tipo_doc', 20)->default('DNI')->after('cabecera_monitoreo_id');
            }
            if (!Schema::hasColumn('mon_equipo_monitoreo', 'doc')) {
                $table->string('doc', 20)->after('tipo_doc')->nullable();
            }
        });

        // 3. OPERACIÓN DE QUIRÓFANO (SQL Nativo)
        // Intentamos limpiar la columna user_id y nombre_completo sin importar las llaves
        try {
            // Intentamos borrar el índice por si existe con el nombre antiguo o nuevo
            DB::statement('ALTER TABLE mon_equipo_monitoreo DROP FOREIGN KEY IF EXISTS monitoreo_equipo_user_id_foreign');
        } catch (\Exception $e) { }

        Schema::table('mon_equipo_monitoreo', function (Blueprint $table) {
            if (Schema::hasColumn('mon_equipo_monitoreo', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('mon_equipo_monitoreo', 'nombre_completo')) {
                $table->dropColumn('nombre_completo');
            }
        });

        // 4. CAMBIO DE PRIMARY KEY
        // Primero nos aseguramos de que 'doc' no tenga nulos (requisito para ser PK)
        DB::statement("UPDATE mon_equipo_monitoreo SET doc = 'PENDIENTE' WHERE doc IS NULL");
        
        Schema::table('mon_equipo_monitoreo', function (Blueprint $table) {
            // Quitamos el auto-incremento del ID y lo borramos
            if (Schema::hasColumn('mon_equipo_monitoreo', 'id')) {
                $table->dropColumn('id');
            }
            // Establecemos 'doc' como Primary Key
            $table->string('doc', 20)->change()->primary();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // No es recomendable hacer rollback de un cambio de PK manual
    }
};