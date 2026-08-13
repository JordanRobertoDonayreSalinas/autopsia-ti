<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tables = [
        'citas_actas',
        'emergencia_actas',
        'farmacia_actas',
        'fua_actas',
        'ges_adm_actas',
        'laboratorio_actas',
        'medicina_actas',
        'mental_actas',
        'nutricion_actas',
        'odontologia_actas',
        'psicologia_actas',
        'referencias_actas',
        'triaje_actas',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (!Schema::hasColumn($table->getTable(), 'foto1')) {
                        $table->string('foto1')->nullable()->after('observaciones');
                    }
                    if (!Schema::hasColumn($table->getTable(), 'foto2')) {
                        $table->string('foto2')->nullable()->after('foto1');
                    }
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $cols = [];
                    if (Schema::hasColumn($table->getTable(), 'foto1')) $cols[] = 'foto1';
                    if (Schema::hasColumn($table->getTable(), 'foto2')) $cols[] = 'foto2';
                    if ($cols) $table->dropColumn($cols);
                });
            }
        }
    }
};
