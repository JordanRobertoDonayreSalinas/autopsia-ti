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
                // Table might already have a primary key, ignore to prevent migration failure
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
                // Ignore errors
            }
        }
    }
};
