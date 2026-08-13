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
        // 2. Componente Capacitación
        Schema::create('com_capacitacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_id')->constrained('mon_cabecera_monitoreo')->onDelete('cascade');
            $table->string('modulo_id')->index(); // Index ayuda a buscar rápido por módulo
            
            $table->foreignId('profesional_id')->constrained('mon_profesionales'); 

            $table->string('recibieron_cap'); // Opciones: SI, NO.
            $table->string('institucion_cap')->nullable(); // Opciones: MINSA, DIRESA, UUEE
            $table->string('decl_jurada'); // Opciones: SI,NO
            $table->string('comp_confidencialidad'); // Opciones: SI, NO
            $table->timestamps();
        });

        // 3. Componente Equipamiento
        // Schema::create('com_equipamiento', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('acta_id')->constrained('mon_cabecera_monitoreo')->onDelete('cascade');
        //     $table->string('modulo_id')->index();
            
        //     $table->foreignId('profesional_id')->constrained('mon_profesionales');

        //     $table->string('descripcion'); // Opciones: MONITOR, CPU, TECLADO, MOUSE, IMPRESORA, TICKETERA, LECTORA DE DNIe
        //     $table->string('cantidad'); // Opciones: siempre sera 1
        //     $table->string('propiedad'); // Opciones: ESTABLECIMIENTO, PERSONAL 
        //     $table->string('estado'); // opciones: BUENO, REGULAR, MALO
        //     $table->string('cod_barras');
        //     $table->string('observaciones');
        //     $table->text('comentarios')->nullable();
        //     $table->timestamps();
        // });

        // 4. Componente Dificultad
        Schema::create('com_dificultad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_id')->constrained('mon_cabecera_monitoreo')->onDelete('cascade');
            $table->string('modulo_id')->index();
            
            $table->foreignId('profesional_id')->constrained('mon_profesionales');

            $table->string('insti_comunica')->nullable(); // Opciones: MINSA, DIRESA, UUEE
            $table->string('medio_comunica')->nullable(); // Opciones: WHATSAPP, ANYDESK, CELULAR
            $table->text('comentarios')->nullable();
            $table->timestamps();
        });

        // 5. Componente Fotos
        Schema::create('com_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_id')->constrained('mon_cabecera_monitoreo')->onDelete('cascade');
            $table->string('modulo_id')->index();
            
            $table->foreignId('profesional_id')->constrained('mon_profesionales');

            $table->string('url_foto')->nullable();
            $table->timestamps();
        });

        // 6. Componente Documentos Asistencias {Inicio de Labores}
        Schema::create('com_docu_asisten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_id')->constrained('mon_cabecera_monitoreo')->onDelete('cascade');
            $table->string('modulo_id')->index();
            
            $table->foreignId('profesional_id')->constrained('mon_profesionales');

            $table->string('cant_consultorios'); 
            $table->string('nombre_consultorio'); 
            $table->string('turno'); // Opciones: MAÑANA, TARDE
            $table->date('fecha_registro');
            $table->string('fua')->nullable(); // Opciones: FUA ELECTRONICA, FUA MANUAL
            $table->string('referencia')->nullable(); // Opciones: REFERENCIA POR SIHCE, DIRECTO A REFCON
            $table->string('receta')->nullable(); // Opciones: RECETA POR SIHCE, RECETA MANUAL
            $table->string('orden_laboratorio')->nullable(); // Opciones: ORDEN POR SIHCE / ORDEN MANUAL
            $table->text('comentarios')->nullable();
            $table->string('utiliza_sihce'); // Opciones: SI, NO, esta campo se utiliza el componente profesional
            $table->timestamps();
        });

        // 7. Componente DNI Electronico
        Schema::create('com_dni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_id')->constrained('mon_cabecera_monitoreo')->onDelete('cascade');
            $table->string('modulo_id')->index();
            
            $table->foreignId('profesional_id')->constrained('mon_profesionales');

            $table->string('tip_dni'); // Opciones: DNI ELECTRONICO, DNI AZUL
            $table->string('version_dni')->nullable(); // Opciones: v1, v2, v3
            $table->string('firma_sihce')->nullable();; // Opciones: SI, NO.
            $table->text('comentarios')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('com_dni');
        Schema::dropIfExists('com_docu_asisten');
        Schema::dropIfExists('com_fotos');
        Schema::dropIfExists('com_dificultad');
        Schema::dropIfExists('com_equipamiento');
        Schema::dropIfExists('com_capacitacion');
    }
};
