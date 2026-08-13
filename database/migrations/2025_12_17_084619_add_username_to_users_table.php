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
        // Agregamos la columna 'username' (DNI) después del nombre.
        // Lo ponemos 'nullable' (opcional) por si ya tienes usuarios antiguos sin DNI, para que no de error.
        $table->string('username', 8)->nullable()->unique()->after('name');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        // Esto es por si quieres deshacer el cambio
        $table->dropColumn('username');
    });
}
};
