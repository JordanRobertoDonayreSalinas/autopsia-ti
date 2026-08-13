<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cambiamos el tipo de columna a string (varchar)
        Schema::table('mon_equipos_computo', function (Blueprint $table) {
            $table->string('propio', 50)->change();
        });

        // 2. Opcional: Convertir los registros existentes de "1/0" a "INSTITUCIONAL/PERSONAL"
        DB::table('mon_equipos_computo')->where('propio', '1')->update(['propio' => 'INSTITUCIONAL']);
        DB::table('mon_equipos_computo')->where('propio', '0')->update(['propio' => 'PERSONAL']);
    }

    public function down(): void
    {
        // En caso de revertir, vuelve a ser integer
        Schema::table('mon_equipos_computo', function (Blueprint $table) {
            $table->integer('propio')->change();
        });
    }
};