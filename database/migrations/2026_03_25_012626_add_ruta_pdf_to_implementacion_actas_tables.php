<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Helpers\ImplementacionHelper;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $modulos = ImplementacionHelper::getModulos();

        foreach ($modulos as $config) {
            $tabla = $config['tabla']; // ej: citas_actas, triaje_actas, etc.
            
            if (Schema::hasTable($tabla) && !Schema::hasColumn($tabla, 'archivo_pdf')) {
                Schema::table($tabla, function (Blueprint $table) {
                    $table->string('archivo_pdf')->nullable()->after('observaciones');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $modulos = ImplementacionHelper::getModulos();

        foreach ($modulos as $config) {
            $tabla = $config['tabla'];
            
            if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'archivo_pdf')) {
                Schema::table($tabla, function (Blueprint $table) {
                    $table->dropColumn('archivo_pdf');
                });
            }
        }
    }
};
