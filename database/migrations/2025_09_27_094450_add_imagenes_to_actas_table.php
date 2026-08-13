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
        Schema::table('actas', function (Blueprint $table) {
            $table->string('imagen1')->nullable()->after('implementador');
            $table->string('imagen2')->nullable()->after('imagen1');
            $table->string('imagen3')->nullable()->after('imagen2');
            $table->string('imagen4')->nullable()->after('imagen3');
            $table->string('imagen5')->nullable()->after('imagen4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actas', function (Blueprint $table) {
            $table->dropColumn(['imagen1', 'imagen2', 'imagen3', 'imagen4', 'imagen5']);
        });
    }
};
