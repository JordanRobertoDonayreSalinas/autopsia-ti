<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id();
            $table->string('dni', 8);
            $table->string('apellidos', 100);
            $table->string('nombres', 100);
            $table->string('celular', 9);
            $table->string('correo');
            $table->string('codigo_ipress');
            $table->string('nombre_establecimiento');
            $table->string('distrito_establecimiento');
            $table->string('provincia_establecimiento');
            $table->string('categoria', 20);
            $table->string('red', 100);
            $table->string('microred', 100);
            $table->string('modulos', 100);
            $table->text('observacion');
            $table->string('imagen1')->nullable();
            $table->string('imagen2')->nullable();
            $table->string('imagen3')->nullable();
            $table->enum('estado', ['Pendiente', 'En proceso', 'Resuelto'])->default('Pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};
