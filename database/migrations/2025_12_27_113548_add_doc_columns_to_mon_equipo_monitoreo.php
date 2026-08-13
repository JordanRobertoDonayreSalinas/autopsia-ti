<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mon_equipo_monitoreo', function (Blueprint $table) {
            // 1. Agregar tipo_doc y doc antes de los apellidos
            // Si apellido_paterno ya existe de la migración anterior, los ponemos antes
            if (!Schema::hasColumn('mon_equipo_monitoreo', 'tipo_doc')) {
                $table->string('tipo_doc', 20)->default('DNI')->after('cabecera_monitoreo_id');
            }
            if (!Schema::hasColumn('mon_equipo_monitoreo', 'doc')) {
                $table->string('doc', 20)->after('tipo_doc')->nullable();
            }

            // 2. Por si acaso la migración anterior falló al borrar, lo hacemos aquí
            Schema::disableForeignKeyConstraints();
            
            try {
                // Quitamos la relación foránea si aún existe
                $table->dropForeign('monitoreo_equipo_user_id_foreign');
            } catch (\Exception $e) { }

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
            $table->dropColumn(['tipo_doc', 'doc']);
        });
    }
};