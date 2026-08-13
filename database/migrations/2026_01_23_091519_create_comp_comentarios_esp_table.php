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
        Schema::create('comp_comentarios_esp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_id')->constrained('mon_cabecera_monitoreo')->onDelete('cascade');
            $table->text('comentario_esp')->nullable();
            $table->string('foto_url_esp')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comp_comentarios_esp');
    }
};
