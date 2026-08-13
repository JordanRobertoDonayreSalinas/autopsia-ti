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
        Schema::create('reuniones', function (Blueprint $table) {
            $table->id();
            $table->string('titulo_reunion');
            $table->date('fecha_reunion');
            $table->time('hora_reunion');
            $table->time('hora_finalizada_reunion')->nullable();
            $table->string('nombre_institucion');
            $table->text('descripcion_general');
            $table->json('acuerdos')->nullable();
            $table->json('comentarios_observaciones')->nullable();
            $table->string('foto_1')->nullable();
            $table->string('foto_2')->nullable();
            $table->json('participantes')->nullable();
            $table->boolean('anulado')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reuniones');
    }
};
