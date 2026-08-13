<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Listado de tablas de implementación según ImplementacionHelper.
     */
    protected $tablas = [
        'ges_adm_actas',
        'citas_actas',
        'triaje_actas',
        'medicina_actas',
        'odontologia_actas',
        'nutricion_actas',
        'psicologia_actas',
        'fua_actas',
        'farmacia_actas',
        'referencias_actas',
        'laboratorio_actas',
        'emergencia_actas',
        'mental_actas'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tablas as $tabla) {
            if (Schema::hasTable($tabla)) {
                Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                    if (!Schema::hasColumn($tabla, 'anulado')) {
                        $table->boolean('anulado')->default(false)->after('archivo_pdf');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tablas as $tabla) {
            if (Schema::hasTable($tabla)) {
                Schema::table($tabla, function (Blueprint $table) use ($tabla) {
                    if (Schema::hasColumn($tabla, 'anulado')) {
                        $table->dropColumn('anulado');
                    }
                });
            }
        }
    }
};
