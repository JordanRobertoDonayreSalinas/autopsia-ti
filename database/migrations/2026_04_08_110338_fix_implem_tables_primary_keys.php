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
        'citas_implem_actas', 
        'emergencia_implem_actas', 
        'farmacia_implem_actas', 
        'fua_implem_actas', 
        'ges_adm_implem_actas', 
        'laboratorio_implem_actas', 
        'medicina_implem_actas', 
        'mental_implem_actas', 
        'nutricion_implem_actas', 
        'odontologia_implem_actas', 
        'psicologia_implem_actas', 
        'referencias_implem_actas', 
        'triaje_implem_actas', 
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
