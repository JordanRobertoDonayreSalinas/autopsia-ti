<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRenipressDataToImplementacionTables extends Migration
{
    /**
     * Listado de todas las tablas de actas de implementación.
     */
    private $tables = [
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
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->json('renipress_data')->nullable()->after('archivo_pdf');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('renipress_data');
                });
            }
        }
    }
}
