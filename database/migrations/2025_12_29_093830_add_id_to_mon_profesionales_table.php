<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mon_profesionales', function (Blueprint $table) {
            // 1. Eliminamos la llave primaria actual (si existe)
            // Usamos DB::statement porque a veces dropPrimary() da problemas en tablas sin ID
            try {
                DB::statement('ALTER TABLE mon_profesionales DROP PRIMARY KEY');
            } catch (\Exception $e) {
                // Si no tiene llave primaria, ignoramos el error y continuamos
            }
        });

        Schema::table('mon_profesionales', function (Blueprint $table) {
            // 2. Ahora agregamos el ID autoincrementable al inicio
            $table->bigIncrements('id')->first();
        });
    }

    public function down(): void
    {
        Schema::table('mon_profesionales', function (Blueprint $table) {
            $table->dropColumn('id');
            // Opcional: Re-establecer 'doc' como primaria si así estaba originalmente
            // $table->primary('doc');
        });
    }
};