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
        Schema::create('mon_croquis_presencia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cabecera_monitoreo_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('cursor_x')->default(0);
            $table->integer('cursor_y')->default(0);
            $table->longText('elements')->nullable();
            $table->longText('connections')->nullable();
            $table->longText('deleted_ids')->nullable();
            $table->timestamp('last_seen_at');

            $table->unique(['cabecera_monitoreo_id', 'user_id']);
            $table->index('last_seen_at');

            $table->foreign('cabecera_monitoreo_id')->references('id')->on('mon_cabecera_monitoreo')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mon_croquis_presencia');
    }
};
