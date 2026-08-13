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
    Schema::table('monitoreo_programacion', function (Blueprint $table) {
        // 1. ELIMINAR CAMPOS ANTIGUOS (solo si existen)
        $camposViejos = [
            'consultorios_programados', 'consultorios_que_cumplen',
            'turno_mañana', 'turno_tarde', 'observaciones_programacion'
        ];
        foreach ($camposViejos as $campo) {
            if (Schema::hasColumn('monitoreo_programacion', $campo)) {
                $table->dropColumn($campo);
            }
        }

        // 2. AGREGAR NUEVOS CAMPOS (solo si NO existen)
        if (!Schema::hasColumn('monitoreo_programacion', 'rrhh_nombre')) {
            $table->string('rrhh_nombre')->nullable();
        }
        if (!Schema::hasColumn('monitoreo_programacion', 'rrhh_dni')) {
            $table->string('rrhh_dni', 8)->nullable();
        }
        if (!Schema::hasColumn('monitoreo_programacion', 'rrhh_telefono')) {
            $table->string('rrhh_telefono')->nullable();
        }
        if (!Schema::hasColumn('monitoreo_programacion', 'rrhh_correo')) {
            $table->string('rrhh_correo')->nullable();
        }
        if (!Schema::hasColumn('monitoreo_programacion', 'odoo')) {
            $table->string('odoo')->nullable();
        }
        if (!Schema::hasColumn('monitoreo_programacion', 'quien_programa_nombre')) {
            $table->string('quien_programa_nombre')->nullable();
        }
        if (!Schema::hasColumn('monitoreo_programacion', 'quien_programa_dni')) {
            $table->string('quien_programa_dni', 8)->nullable();
        }
        if (!Schema::hasColumn('monitoreo_programacion', 'quien_programa_telefono')) {
            $table->string('quien_programa_telefono')->nullable();
        }
        if (!Schema::hasColumn('monitoreo_programacion', 'quien_programa_correo')) {
            $table->string('quien_programa_correo')->nullable();
        }
        if (!Schema::hasColumn('monitoreo_programacion', 'capacitacion')) {
            $table->string('capacitacion')->nullable();
        }
        if (!Schema::hasColumn('monitoreo_programacion', 'mes_sistema')) {
            $table->string('mes_sistema')->nullable();
        }
        if (!Schema::hasColumn('monitoreo_programacion', 'servicios')) {
            $table->json('servicios')->nullable();
        }
        if (!Schema::hasColumn('monitoreo_programacion', 'comentarios')) {
            $table->text('comentarios')->nullable();
        }
        if (!Schema::hasColumn('monitoreo_programacion', 'entrevistado_nombre')) {
            $table->string('entrevistado_nombre')->nullable();
        }
    });
}
public function down(): void
{
    // En caso de revertir, recreamos los campos antiguos (opcional)
    Schema::table('monitoreo_programacion', function (Blueprint $table) {
        $table->integer('consultorios_programados')->nullable();
        // ... etc
    });
}
};
