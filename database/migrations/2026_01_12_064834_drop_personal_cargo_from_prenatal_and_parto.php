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
        $tablas = ['mon_modulo_prenatal', 'mon_modulo_parto'];

        foreach ($tablas as $nombreTabla) {
            if (Schema::hasColumn($nombreTabla, 'personal_cargo')) {
                Schema::table($nombreTabla, function (Blueprint $table) {
                    $table->dropColumn('personal_cargo');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tablas = ['mon_modulo_prenatal', 'mon_modulo_parto'];

        foreach ($tablas as $nombreTabla) {
            Schema::table($nombreTabla, function (Blueprint $table) {
                // Si revertimos, la volvemos a crear
                $table->string('personal_cargo', 100)->nullable()->after('personal_nombre');
            });
        }
    }
};
