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
        Schema::table('mon_cabecera_monitoreo', function (Blueprint $table) {
            $table->integer('pozo_tierra_operativos')->nullable()->after('pozo_tierra_cantidad');
            $table->integer('pozo_tierra_inoperativos')->nullable()->after('pozo_tierra_operativos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mon_cabecera_monitoreo', function (Blueprint $table) {
            $table->dropColumn(['pozo_tierra_operativos', 'pozo_tierra_inoperativos']);
        });
    }
};
