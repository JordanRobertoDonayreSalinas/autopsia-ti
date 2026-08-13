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
        Schema::table('establecimientos', function (Blueprint $table) {
            if (!Schema::hasColumn('establecimientos', 'institucion')) {
                $table->string('institucion')->nullable()->after('nombre');
            }
            if (!Schema::hasColumn('establecimientos', 'direccion')) {
                $table->text('direccion')->nullable()->after('institucion');
            }
            if (!Schema::hasColumn('establecimientos', 'departamento')) {
                $table->string('departamento')->nullable()->after('direccion');
            }
            if (!Schema::hasColumn('establecimientos', 'centro_poblado')) {
                $table->string('centro_poblado')->nullable()->after('distrito');
            }
            if (!Schema::hasColumn('establecimientos', 'telefono')) {
                $table->string('telefono')->nullable()->after('centro_poblado');
            }
            if (!Schema::hasColumn('establecimientos', 'correo')) {
                $table->string('correo')->nullable()->after('telefono');
            }
            if (!Schema::hasColumn('establecimientos', 'altitud')) {
                $table->string('altitud')->nullable()->after('longitud');
            }
            if (!Schema::hasColumn('establecimientos', 'fecha_creacion_resolucion')) {
                $table->string('fecha_creacion_resolucion')->nullable()->after('altitud');
            }
            if (!Schema::hasColumn('establecimientos', 'fecha_registro')) {
                $table->string('fecha_registro')->nullable()->after('fecha_creacion_resolucion');
            }
            if (!Schema::hasColumn('establecimientos', 'numero_resolucion_creacion')) {
                $table->string('numero_resolucion_creacion')->nullable()->after('fecha_registro');
            }
            if (!Schema::hasColumn('establecimientos', 'horario_atencion')) {
                $table->text('horario_atencion')->nullable()->after('numero_resolucion_creacion');
            }
            if (!Schema::hasColumn('establecimientos', 'numero_ambientes')) {
                $table->string('numero_ambientes')->nullable()->after('horario_atencion');
            }
            if (!Schema::hasColumn('establecimientos', 'numero_camas')) {
                $table->string('numero_camas')->nullable()->after('numero_ambientes');
            }
            if (!Schema::hasColumn('establecimientos', 'colegio_profesional')) {
                $table->string('colegio_profesional')->nullable()->after('numero_documento');
            }
            if (!Schema::hasColumn('establecimientos', 'colegiatura')) {
                $table->string('colegiatura')->nullable()->after('colegio_profesional');
            }
            if (!Schema::hasColumn('establecimientos', 'rne')) {
                $table->string('rne')->nullable()->after('colegiatura');
            }
            if (!Schema::hasColumn('establecimientos', 'clas')) {
                $table->string('clas')->nullable()->after('microred');
            }
            if (!Schema::hasColumn('establecimientos', 'odsis')) {
                $table->string('odsis')->nullable()->after('clas');
            }
            if (!Schema::hasColumn('establecimientos', 'estado')) {
                $table->string('estado')->nullable()->after('categoria');
            }
            if (!Schema::hasColumn('establecimientos', 'condicion')) {
                $table->string('condicion')->nullable()->after('estado');
            }
            if (!Schema::hasColumn('establecimientos', 'upss')) {
                $table->json('upss')->nullable()->after('condicion');
            }
            if (!Schema::hasColumn('establecimientos', 'ups')) {
                $table->json('ups')->nullable()->after('upss');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('establecimientos', function (Blueprint $table) {
            $table->dropColumn([
                'institucion', 'direccion', 'departamento', 'centro_poblado',
                'telefono', 'correo', 'altitud', 'fecha_creacion_resolucion',
                'fecha_registro', 'numero_resolucion_creacion', 'horario_atencion',
                'numero_ambientes', 'numero_camas', 'colegio_profesional',
                'colegiatura', 'rne', 'clas', 'odsis', 'estado', 'condicion',
                'upss', 'ups'
            ]);
        });
    }
};
