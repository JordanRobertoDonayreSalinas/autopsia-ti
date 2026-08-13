<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('mon_cabecera_monitoreo', function (Blueprint $table) {
        $table->string('categoria_congelada')->nullable()->after('establecimiento_id');
        $table->string('responsable_congelado')->nullable()->after('categoria_congelada');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cabecera_monitoreo', function (Blueprint $table) {
            //
        });
    }
};
