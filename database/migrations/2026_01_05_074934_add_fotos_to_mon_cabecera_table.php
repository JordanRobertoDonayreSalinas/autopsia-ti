<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mon_cabecera_monitoreo', function (Blueprint $table) {
            // Agregamos foto1 y foto2 después del campo responsable
            $table->string('foto1')->nullable()->after('responsable');
            $table->string('foto2')->nullable()->after('foto1');
        });
    }

    public function down(): void
    {
        Schema::table('mon_cabecera_monitoreo', function (Blueprint $table) {
            $table->dropColumn(['foto1', 'foto2']);
        });
    }
};