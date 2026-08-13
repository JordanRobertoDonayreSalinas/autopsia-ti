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
        // 1. Actualizamos la tabla PRENATAL
        Schema::table('mon_modulo_prenatal', function (Blueprint $table) {
            $table->string('firma_dj', 2)->nullable()->comment('SI/NO Declaración Jurada')->after('personal_nombre');
            $table->string('firma_confidencialidad', 2)->nullable()->comment('SI/NO Confidencialidad')->after('firma_dj');

            $table->string('tipo_dni_fisico', 20)->nullable()->comment('ELECTRONICO / AZUL')->after('firma_confidencialidad');
            $table->string('dnie_version', 10)->nullable()->comment('1.0, 2.0, 3.0')->after('tipo_dni_fisico');
            $table->string('firma_sihce', 2)->nullable()->comment('SI/NO Firma Digital')->after('dnie_version');
        });

        // 2. Actualizamos la tabla PARTO
        Schema::table('mon_modulo_parto', function (Blueprint $table) {
            // Verificamos primero si existe la columna de referencia, si no, lo agrega al final
            $afterColumn = Schema::hasColumn('mon_modulo_parto', 'personal_nombre') ? 'personal_nombre' : null;

            $table->string('firma_dj', 2)->nullable()->comment('SI/NO Declaración Jurada')->after($afterColumn);
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
        $campos = [
            'firma_dj',
            'firma_confidencialidad',
            'tipo_dni_fisico',
            'dnie_version',
            'firma_sihce'
        ];

        Schema::table('mon_modulo_prenatal', function (Blueprint $table) use ($campos) {
            $table->dropColumn($campos);
        });

        Schema::table('mon_modulo_parto', function (Blueprint $table) use ($campos) {
            $table->dropColumn($campos);
        });
    }
};
