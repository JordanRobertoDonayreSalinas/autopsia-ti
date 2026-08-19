<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Identificador de origen offline.
 *
 * La app de campo genera un UUID por acta/reunion ANTES de tener conexion. Al
 * sincronizar lo envia junto al registro; el backend lo guarda para poder
 * detectar reenvios (reintentos con backoff, sincronizaciones duplicadas) y
 * devolver el id real sin crear filas repetidas.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('mon_cabecera_monitoreo', 'offline_uuid')) {
            Schema::table('mon_cabecera_monitoreo', function (Blueprint $table) {
                $table->string('offline_uuid', 64)->nullable()->unique()->after('id');
            });
        }

        if (!Schema::hasColumn('reuniones', 'offline_uuid')) {
            Schema::table('reuniones', function (Blueprint $table) {
                $table->string('offline_uuid', 64)->nullable()->unique()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mon_cabecera_monitoreo', 'offline_uuid')) {
            Schema::table('mon_cabecera_monitoreo', function (Blueprint $table) {
                $table->dropUnique(['offline_uuid']);
                $table->dropColumn('offline_uuid');
            });
        }

        if (Schema::hasColumn('reuniones', 'offline_uuid')) {
            Schema::table('reuniones', function (Blueprint $table) {
                $table->dropUnique(['offline_uuid']);
                $table->dropColumn('offline_uuid');
            });
        }
    }
};
