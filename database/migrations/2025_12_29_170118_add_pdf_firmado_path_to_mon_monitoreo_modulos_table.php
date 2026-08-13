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
    Schema::table('mon_monitoreo_modulos', function (Blueprint $table) {
        // Agregamos la columna para guardar la ruta del archivo PDF firmado
        $table->string('pdf_firmado_path')->nullable()->after('contenido');
    });
}

public function down()
{
    Schema::table('mon_monitoreo_modulos', function (Blueprint $table) {
        $table->dropColumn('pdf_firmado_path');
    });
}
};
