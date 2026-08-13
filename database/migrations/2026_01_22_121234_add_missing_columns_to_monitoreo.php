<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Asegúrate de que el nombre de la tabla sea EXACTAMENTE 'mon_cabecera_monitoreo'
        Schema::table('mon_cabecera_monitoreo', function (Blueprint $table) {
            
            // 1. Agregar tipo_origen si no existe
            if (!Schema::hasColumn('mon_cabecera_monitoreo', 'tipo_origen')) {
                // Lo ponemos después de 'establecimiento_id' o al final si no importa
                $table->string('tipo_origen', 20)->default('ESTANDAR')->after('establecimiento_id');
            }

            // 2. Agregar numero_acta si no existe
            if (!Schema::hasColumn('mon_cabecera_monitoreo', 'numero_acta')) {
                $table->integer('numero_acta')->default(0)->after('tipo_origen');
            }
        });

        // 3. Llenar los datos vacíos para evitar errores con actas viejas
        // Si hay registros, les asignamos su mismo ID como número de acta
        DB::statement("UPDATE mon_cabecera_monitoreo SET numero_acta = id WHERE numero_acta = 0");
    }

    public function down()
    {
        Schema::table('mon_cabecera_monitoreo', function (Blueprint $table) {
            if (Schema::hasColumn('mon_cabecera_monitoreo', 'tipo_origen')) {
                $table->dropColumn('tipo_origen');
            }
            if (Schema::hasColumn('mon_cabecera_monitoreo', 'numero_acta')) {
                $table->dropColumn('numero_acta');
            }
        });
    }
};