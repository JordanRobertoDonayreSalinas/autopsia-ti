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
        Schema::table('documentos_administrativos', function (Blueprint $table) {
            // Agregamos teléfono y cargo del profesional
            // Los ponemos 'nullable' para compatibilidad con registros existentes
            $table->string('profesional_telefono')->nullable()->after('correo_electronico');
            $table->string('profesional_cargo')->nullable()->after('profesional_telefono');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos_administrativos', function (Blueprint $table) {
            $table->dropColumn(['profesional_telefono', 'profesional_cargo']);
        });
    }
};
