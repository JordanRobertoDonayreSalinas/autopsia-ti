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
            $table->string('pozo_tierra', 5)->default('NO')->after('responsable');
            $table->integer('pozo_tierra_cantidad')->nullable()->after('pozo_tierra');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mon_cabecera_monitoreo', function (Blueprint $table) {
            $table->dropColumn(['pozo_tierra', 'pozo_tierra_cantidad']);
        });
    }
};
