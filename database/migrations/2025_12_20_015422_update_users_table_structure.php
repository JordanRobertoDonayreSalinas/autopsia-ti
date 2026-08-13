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
        Schema::table('users', function (Blueprint $table) {
            // 1. Añadimos los apellidos solo si no existen
            if (!Schema::hasColumn('users', 'apellido_paterno')) {
                $table->string('apellido_paterno')->after('id')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'apellido_materno')) {
                $table->string('apellido_materno')->after('apellido_paterno')->nullable();
            }
            
            // 2. Añadimos el estado (status) solo si no existe
            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('role');
            }

            // 3. Eliminamos 'is_active' por redundancia
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminamos las columnas nuevas
            $table->dropColumn(['apellido_paterno', 'apellido_materno', 'status']);
            
            // Restauramos la columna original 'is_active' si no existe
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('password');
            }
        });
    }
};