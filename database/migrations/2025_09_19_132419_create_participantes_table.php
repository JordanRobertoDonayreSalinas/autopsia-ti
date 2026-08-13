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
        Schema::create('participantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('acta_id');
            $table->string('dni', 15);
            $table->string('apellidos');
            $table->string('nombres');
            $table->string('cargo')->nullable();
            $table->string('modulo')->nullable();
            $table->timestamps();

            // Relación con actas
            $table->foreign('acta_id')
                  ->references('id')->on('actas')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participantes');
    }
};
