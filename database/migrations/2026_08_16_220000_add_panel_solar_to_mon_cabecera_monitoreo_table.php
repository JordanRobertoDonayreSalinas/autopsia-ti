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
        Schema::table('mon_cabecera_monitoreo', function (Blueprint $table) {
            $table->string('panel_solar', 5)->default('NO')->after('pozo_tierra_inoperativos');
            $table->integer('panel_solar_cantidad')->nullable()->after('panel_solar');
            $table->integer('panel_solar_operativos')->nullable()->after('panel_solar_cantidad');
            $table->integer('panel_solar_inoperativos')->nullable()->after('panel_solar_operativos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mon_cabecera_monitoreo', function (Blueprint $table) {
            $table->dropColumn(['panel_solar', 'panel_solar_cantidad', 'panel_solar_operativos', 'panel_solar_inoperativos']);
        });
    }
};
