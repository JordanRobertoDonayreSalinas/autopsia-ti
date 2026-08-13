<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    protected $tables = [
        'citas_usu_actas', 'citas_impl_actas',
        'emergencia_usu_actas', 'emergencia_impl_actas',
        'farmacia_usu_actas', 'farmacia_impl_actas',
        'fua_usu_actas', 'fua_impl_actas',
        'ges_adm_usu_actas', 'ges_adm_impl_actas',
        'laboratorio_usu_actas', 'laboratorio_impl_actas',
        'medicina_usu_actas', 'medicina_impl_actas',
        'mental_usu_actas', 'mental_impl_actas',
        'nutricion_usu_actas', 'nutricion_impl_actas',
        'odontologia_usu_actas', 'odontologia_impl_actas',
        'psicologia_usu_actas', 'psicologia_impl_actas',
        'referencias_usu_actas', 'referencias_impl_actas',
        'triaje_usu_actas', 'triaje_impl_actas',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY;");
                }
            } catch (\Exception $e) {
                // Ignore failure if already exists
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `id` BIGINT(20) UNSIGNED NOT NULL;");
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE `{$table}` DROP PRIMARY KEY;");
                }
            } catch (\Exception $e) {
                // Ignore failure
            }
        }
    }
};
