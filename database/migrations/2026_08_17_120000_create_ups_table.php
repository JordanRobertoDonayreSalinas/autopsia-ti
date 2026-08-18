<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * La tabla ya existe en algunos entornos (se creó manualmente por
     * phpMyAdmin); el guard evita que falle ahí y crea la tabla desde cero
     * en cualquier entorno nuevo.
     */
    public function up(): void
    {
        if (Schema::hasTable('ups')) {
            return;
        }

        Schema::create('ups', function (Blueprint $table) {
            $table->id();
            $table->string('hospital_codigo', 20);
            $table->string('hospital_nombre', 200);
            $table->string('codigo', 20);
            $table->string('nombre', 255);
            $table->string('estado', 20);

            $table->unique(['hospital_codigo', 'codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ups');
    }
};
