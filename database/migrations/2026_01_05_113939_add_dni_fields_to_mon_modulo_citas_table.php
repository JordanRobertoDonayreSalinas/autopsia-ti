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
        Schema::table('mon_modulo_citas', function (Blueprint $table) {
            // Agregamos los campos después de 'personal_roles' para mantener el orden lógico
            $table->string('firma_dj', 2)->nullable()->comment('SI/NO Declaración Jurada')->after('personal_roles');
            $table->string('firma_confidencialidad', 2)->nullable()->comment('SI/NO Confidencialidad')->after('firma_dj');

            $table->string('tipo_dni_fisico', 20)->nullable()->comment('ELECTRONICO / AZUL')->after('firma_confidencialidad');
            $table->string('dnie_version', 10)->nullable()->comment('1.0, 2.0, 3.0')->after('tipo_dni_fisico');
            $table->string('firma_sihce', 2)->nullable()->comment('SI/NO Firma Digital')->after('dnie_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mon_modulo_citas', function (Blueprint $table) {
            $table->dropColumn([
                'firma_dj',
                'firma_confidencialidad',
                'tipo_dni_fisico',
                'dnie_version',
                'firma_sihce'
            ]);
        });
    }
};
