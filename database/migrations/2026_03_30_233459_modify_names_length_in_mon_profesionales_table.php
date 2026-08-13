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
            $table->string('apellido_paterno', 50)->nullable()->change();
            $table->string('apellido_materno', 50)->nullable()->change();
            $table->string('nombres', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mon_profesionales', function (Blueprint $table) {
            $table->string('apellido_paterno', 24)->nullable()->change();
            $table->string('apellido_materno', 13)->nullable()->change();
            $table->string('nombres', 32)->nullable()->change();
        });
    }
};
