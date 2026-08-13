<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('croquis_colaboracion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('acta_id');
            $table->unsignedBigInteger('user_id');
            $table->string('user_name', 120);
            $table->string('color', 10)->default('#4f46e5');
            $table->float('cursor_x')->default(0);
            $table->float('cursor_y')->default(0);
            $table->json('elements')->nullable();
            $table->json('connections')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['acta_id', 'user_id']);
            $table->index(['acta_id', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('croquis_colaboracion');
    }
};
