<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mon_equipo_monitoreo', function (Blueprint $table) {
            // 1. Crear columnas solo si NO existen para evitar el error "Duplicate column"
            if (!Schema::hasColumn('mon_equipo_monitoreo', 'apellido_paterno')) {
                $table->string('apellido_paterno')->after('cabecera_monitoreo_id')->nullable();
            }
            if (!Schema::hasColumn('mon_equipo_monitoreo', 'apellido_materno')) {
                $table->string('apellido_materno')->after('apellido_paterno')->nullable();
            }
            if (!Schema::hasColumn('mon_equipo_monitoreo', 'nombres')) {
                $table->string('nombres')->after('apellido_materno')->nullable();
            }
        });

        // 2. Mover datos de respaldo
        DB::statement("UPDATE mon_equipo_monitoreo SET nombres = nombre_completo WHERE nombre_completo IS NOT NULL AND nombres IS NULL");

        Schema::table('mon_equipo_monitoreo', function (Blueprint $table) {
            // 3. Desactivar llaves foráneas temporalmente para limpiar
            Schema::disableForeignKeyConstraints();

            // 4. Intentar borrar la llave foránea por nombre (el error anterior decía que existía)
            // Usamos try-catch por si ya se hubiera borrado en un intento fallido
            try {
                $table->dropForeign('monitoreo_equipo_user_id_foreign');
            } catch (\Exception $e) {
                // Si no existe con el nombre anterior, intentamos con el nombre estándar
                try { $table->dropForeign(['user_id']); } catch (\Exception $e2) {}
            }

            // 5. Borrar las columnas viejas
            if (Schema::hasColumn('mon_equipo_monitoreo', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('mon_equipo_monitoreo', 'nombre_completo')) {
                $table->dropColumn('nombre_completo');
            }

            Schema::enableForeignKeyConstraints();
        });
    }

    public function down(): void
    {
        Schema::table('mon_equipo_monitoreo', function (Blueprint $table) {
            if (!Schema::hasColumn('mon_equipo_monitoreo', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('mon_equipo_monitoreo', 'nombre_completo')) {
                $table->string('nombre_completo')->after('user_id')->nullable();
            }
            $table->dropColumn(['apellido_paterno', 'apellido_materno', 'nombres']);
        });
    }
};