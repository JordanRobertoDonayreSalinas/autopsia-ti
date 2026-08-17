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
        Schema::table('mon_equipos_computo', function (Blueprint $table) {
            if (!Schema::hasColumn('mon_equipos_computo', 'especificaciones')) {
                $table->json('especificaciones')->nullable()->after('observacion');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mon_equipos_computo', function (Blueprint $table) {
            if (Schema::hasColumn('mon_equipos_computo', 'especificaciones')) {
                $table->dropColumn('especificaciones');
            }
        });
    }
};
