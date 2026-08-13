<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Renombrar el módulo en la tabla de módulos base
        \Illuminate\Support\Facades\DB::table('mon_monitoreo_modulos')
            ->where('modulo_nombre', 'infraestructura_3d')
            ->update(['modulo_nombre' => 'infraestructura_2d']);

        // 2. Renombrar el módulo en la tabla de detalles (si existe información allí)
        \Illuminate\Support\Facades\DB::table('mon_detalle_modulos')
            ->where('modulo_nombre', 'infraestructura_3d')
            ->update(['modulo_nombre' => 'infraestructura_2d']);

        // 3. Actualizar la configuración de módulos activos en cada acta
        $configs = \Illuminate\Support\Facades\DB::table('mon_monitoreo_modulos')
            ->where('modulo_nombre', 'config_modulos')
            ->get();

        foreach ($configs as $config) {
            $contenido = json_decode($config->contenido, true);
            if (is_array($contenido)) {
                $index = array_search('infraestructura_3d', $contenido);
                if ($index !== false) {
                    $contenido[$index] = 'infraestructura_2d';
                    \Illuminate\Support\Facades\DB::table('mon_monitoreo_modulos')
                        ->where('id', $config->id)
                        ->update(['contenido' => json_encode($contenido)]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Revertir en tablas principales
        \Illuminate\Support\Facades\DB::table('mon_monitoreo_modulos')
            ->where('modulo_nombre', 'infraestructura_2d')
            ->update(['modulo_nombre' => 'infraestructura_3d']);

        \Illuminate\Support\Facades\DB::table('mon_detalle_modulos')
            ->where('modulo_nombre', 'infraestructura_2d')
            ->update(['modulo_nombre' => 'infraestructura_3d']);

        // 2. Revertir en configuración
        $configs = \Illuminate\Support\Facades\DB::table('mon_monitoreo_modulos')
            ->where('modulo_nombre', 'config_modulos')
            ->get();

        foreach ($configs as $config) {
            $contenido = json_decode($config->contenido, true);
            if (is_array($contenido)) {
                $index = array_search('infraestructura_2d', $contenido);
                if ($index !== false) {
                    $contenido[$index] = 'infraestructura_3d';
                    \Illuminate\Support\Facades\DB::table('mon_monitoreo_modulos')
                        ->where('id', $config->id)
                        ->update(['contenido' => json_encode($contenido)]);
                }
            }
        }
    }
};
