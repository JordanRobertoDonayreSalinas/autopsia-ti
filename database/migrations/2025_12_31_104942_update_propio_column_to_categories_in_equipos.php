<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Primero nos aseguramos de que la columna sea VARCHAR para soportar los textos
        Schema::table('mon_equipos_computo', function (Blueprint $blueprint) {
            $blueprint->string('propio', 50)->nullable()->change();
        });

        // 2. Mapeo de datos existentes para no perder información
        // Si era '1' o 'SI' o 'INSTITUCIONAL' -> Ahora es ESTABLECIMIENTO
        DB::table('mon_equipos_computo')
            ->whereIn('propio', ['1', 'SI', 'INSTITUCIONAL'])
            ->update(['propio' => 'ESTABLECIMIENTO']);

        // Si era '0' o 'NO' -> Ahora es PERSONAL
        DB::table('mon_equipos_computo')
            ->whereIn('propio', ['0', 'NO'])
            ->update(['propio' => 'PERSONAL']);

        // 3. Cualquier valor que no sea ESTABLECIMIENTO o SERVICIO, lo ponemos como PERSONAL por seguridad
        DB::table('mon_equipos_computo')
            ->whereNotIn('propio', ['ESTABLECIMIENTO', 'SERVICIO', 'PERSONAL'])
            ->update(['propio' => 'PERSONAL']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mon_equipos_computo', function (Blueprint $table) {
            // Si reviertes, vuelve a ser un string simple o podrías regresarlo a integer
            $table->string('propio', 255)->change();
        });
    }
};