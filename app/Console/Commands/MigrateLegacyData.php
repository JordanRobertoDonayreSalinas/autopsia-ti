<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Exception;

class MigrateLegacyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrar:legacy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra dinámicamente TODOS los datos legacy de temp_ai y temp_sa al esquema local estructurado.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Iniciando Migración Legacy Dinámica (TODO EL CONTENIDO)...");

        try {
            DB::connection('mysql_temp_ai')->getPdo();
            DB::connection('mysql_temp_sa')->getPdo();
        } catch (Exception $e) {
            $this->error("No se pudo conectar a temp_ai o temp_sa. Detalle del error: " . $e->getMessage());
            return 1;
        }

        // Tablas que se deben omitir porque son internas de config de Laravel
        $tablasIgnoradas = [
            'migrations', 'failed_jobs', 'personal_access_tokens', 
            'password_reset_tokens', 'password_resets', 'sessions', 
            'cache', 'cache_locks', 'jobs', 'job_batches'
        ];

        DB::beginTransaction();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            $this->migrarBaseDeDatos('mysql_temp_ai', 'Implementación (temp_ai)', $tablasIgnoradas);
            $this->migrarBaseDeDatos('mysql_temp_sa', 'Asistencia Técnica (temp_sa)', $tablasIgnoradas);

            DB::commit();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->info("¡Migración Legacy Total completada exitosamente!");
            return 0;

        } catch (Exception $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error("Fallo durante la migración: " . $e->getMessage());
            return 1;
        }
    }

    private function migrarBaseDeDatos($conexion, $nombre, $ignoradas)
    {
        $this->info("=== MIGRANDO {$nombre} ===");
        
        $tablasObjeto = DB::connection($conexion)->select("SHOW TABLES");
        
        foreach ($tablasObjeto as $t) {
            $tablaTemp = array_values((array)$t)[0];

            if (in_array($tablaTemp, $ignoradas)) continue;

            if (Schema::hasTable($tablaTemp)) {
                $registros = DB::connection($conexion)->table($tablaTemp)->get();
                $this->line("-> Migrando tabla '{$tablaTemp}' (" . count($registros) . " registros)...");
                
                $columnasDestino = Schema::getColumnListing($tablaTemp);

                foreach ($registros as $reg) {
                    $arr = (array)$reg;
                    $insertData = [];
                    foreach ($columnasDestino as $columna) {
                        if (array_key_exists($columna, $arr)) {
                            $insertData[$columna] = $arr[$columna];
                        }
                    }
                    
                    if (!empty($insertData)) {
                        try {
                            // Se utiliza insertOrIgnore para omitir registros ya existentes (por ejemplo las cabeceras)
                            DB::table($tablaTemp)->insertOrIgnore($insertData);
                        } catch (Exception $ex) {
                            // Silenciar el error en caso de fallo por row
                        }
                    }
                }
            } else {
                $this->warn("La tabla {$tablaTemp} no existe en la base de datos unificada local, se omite.");
            }
        }
    }
}
