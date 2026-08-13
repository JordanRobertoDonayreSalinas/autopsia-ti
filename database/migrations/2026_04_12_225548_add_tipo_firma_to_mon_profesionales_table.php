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
        Schema::table('mon_profesionales', function (Blueprint $table) {
            $table->string('tipo_firma', 20)->default('MANUAL')->after('firma_path')->comment('MANUAL, DIGITAL, NONE');
        });
    }

    public function down(): void
    {
        Schema::table('mon_profesionales', function (Blueprint $table) {
            $table->dropColumn('tipo_firma');
        });
    }
};
