<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mon_cabecera_monitoreo', function (Blueprint $table) {
            $table->boolean('anulado')->default(false)->after('firmado');
        });
    }

    public function down(): void
    {
        Schema::table('mon_cabecera_monitoreo', function (Blueprint $table) {
            $table->dropColumn('anulado');
        });
    }
};
