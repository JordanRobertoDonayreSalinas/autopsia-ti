<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('programacion_sectores_propuesta')) {
            Schema::create('programacion_sectores_propuesta', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('establecimiento_id')->nullable();
                $table->foreign('establecimiento_id')->references('id')->on('establecimientos')->nullOnDelete();
                $table->string('nombre_pdf', 255);
                $table->string('provincia', 100)->nullable();
                $table->tinyInteger('sector');
                $table->string('cuadril', 15)->nullable();
                $table->date('comienzo')->nullable();
                $table->date('fin')->nullable();
                $table->integer('dias')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('programacion_sectores_propuesta');
    }
};
