<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('mon_respuesta_entrevistado');

        // 1. Homologar la tabla padre (mon_profesionales)
        // Aseguramos que sea InnoDB y que 'doc' sea Primary Key o Unique con el charset correcto
        DB::statement("ALTER TABLE mon_profesionales ENGINE = InnoDB");
        DB::statement("ALTER TABLE mon_profesionales MODIFY doc VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
        
        try {
            DB::statement("ALTER TABLE mon_profesionales ADD PRIMARY KEY (doc)");
        } catch (\Exception $e) {
            // Si ya tiene PK, intentamos asegurar que sea UNIQUE al menos
            try { DB::statement("ALTER TABLE mon_profesionales ADD UNIQUE (doc)"); } catch (\Exception $e2) {}
        }

        // 2. Crear la tabla hija con el mismo charset exacto
        Schema::create('mon_respuesta_entrevistado', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cabecera_monitoreo_id');
            
            // Forzamos el mismo charset y collation que la tabla padre
            $table->string('doc_profesional', 20)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');

            $table->string('modulo');
            $table->boolean('recibio_capacitacion')->default(false);
            $table->string('inst_que_lo_capacito')->nullable();
            $table->string('inst_a_quien_comunica')->nullable();
            $table->string('medio_que_utiliza')->nullable();
            $table->timestamps();
        });

        // 3. Agregar las llaves foráneas mediante SQL Nativo para evitar errores de Laravel
        DB::statement("ALTER TABLE mon_respuesta_entrevistado ADD CONSTRAINT fk_resp_cabecera 
            FOREIGN KEY (cabecera_monitoreo_id) REFERENCES mon_cabecera_monitoreo(id) ON DELETE CASCADE");

        DB::statement("ALTER TABLE mon_respuesta_entrevistado ADD CONSTRAINT fk_resp_prof_doc 
            FOREIGN KEY (doc_profesional) REFERENCES mon_profesionales(doc) ON DELETE RESTRICT");

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('mon_respuesta_entrevistado');
    }
};