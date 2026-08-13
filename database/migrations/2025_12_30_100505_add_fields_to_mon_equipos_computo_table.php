<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('mon_equipos_computo', function (Blueprint $table) {
        // Agregamos nro_serie después del campo 'estado'
        $table->string('nro_serie')->nullable()->after('estado'); 
        
        // Agregamos observacion después de 'propio'
        $table->text('observacion')->nullable()->after('propio');
    });
}

public function down(): void
{
    Schema::table('mon_equipos_computo', function (Blueprint $table) {
        $table->dropColumn(['nro_serie', 'observacion']);
    });
}
};