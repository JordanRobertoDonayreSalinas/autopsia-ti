<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // AQUI ESTABA EL ERROR: Usamos el nombre real de tu tabla
        Schema::table('mon_cabecera_monitoreo', function (Blueprint $table) {
            
            // Verificamos si no existen para no dar error si la corres de nuevo
            if (!Schema::hasColumn('mon_cabecera_monitoreo', 'tipo_origen')) {
                $table->string('tipo_origen', 20)->default('ESTANDAR')->after('establecimiento_id');
            }
            
            if (!Schema::hasColumn('mon_cabecera_monitoreo', 'numero_acta')) {
                $table->integer('numero_acta')->default(0)->after('tipo_origen');
            }
        });

        // Actualizamos los registros viejos para que tengan un número (usamos su ID actual)
        DB::statement("UPDATE mon_cabecera_monitoreo SET numero_acta = id WHERE numero_acta = 0");
    }

    public function down()
    {
        Schema::table('mon_cabecera_monitoreo', function (Blueprint $table) {
            $table->dropColumn(['tipo_origen', 'numero_acta']);
        });
    }
};