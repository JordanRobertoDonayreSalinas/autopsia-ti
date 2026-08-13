<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MonitoreoModulos;
use Illuminate\Support\Facades\DB; // <-- ¡Importante agregar esto!

class ConectividadSeeder extends Seeder
{
    public function run()
    {
        $filePath = database_path('seeders/csv/insercion_conectividad.csv');
        
        if (!file_exists($filePath)) {
            $this->command->error("El archivo CSV no existe en: $filePath");
            return;
        }

        $file = fopen($filePath, 'r');
        fgetcsv($file, 0, ';'); // Omitir cabecera

        while (($row = fgetcsv($file, 0, ';')) !== FALSE) {
            if (count($row) < 7) {
                continue;
            }
            
            $cabeceraId = $row[0];
            // CLAVE 1: Convertimos a minúscula porque tu CredController usa 'cred'
            $modulo     = strtolower(trim($row[3])); 
            $tipo       = trim($row[4]);
            $wifi       = trim($row[5]) === '' ? null : trim($row[5]);
            $operador   = trim($row[6]) === '' ? null : trim($row[6]);

            $actualizado = false;

            // --- ACTUALIZACIÓN EN TABLA 1: mon_monitoreo_modulos (Tu Modelo) ---
            $registro = MonitoreoModulos::where('cabecera_monitoreo_id', $cabeceraId)
                                        ->where('modulo_nombre', $modulo)
                                        ->first();
            if ($registro) {
                $contenido = $registro->contenido ?? [];
                $contenido['tipo_conectividad'] = $tipo;
                $contenido['wifi_fuente']       = $wifi;
                $contenido['operador_servicio'] = $operador;
                
                $registro->contenido = $contenido;
                $registro->save();
                $actualizado = true;
            }

            // --- ACTUALIZACIÓN EN TABLA 2: mon_detalle_modulos (La que manda en la Vista) ---
            $detalle = DB::table('mon_detalle_modulos')
                        ->where('cabecera_monitoreo_id', $cabeceraId)
                        ->where('modulo_nombre', $modulo)
                        ->first();
            if ($detalle) {
                // Decodificamos el JSON bruto de la base de datos
                $contDetalle = is_string($detalle->contenido) ? json_decode($detalle->contenido, true) : (array)$detalle->contenido;
                
                $contDetalle['tipo_conectividad'] = $tipo;
                $contDetalle['wifi_fuente']       = $wifi;
                $contDetalle['operador_servicio'] = $operador;
                
                DB::table('mon_detalle_modulos')
                    ->where('cabecera_monitoreo_id', $cabeceraId)
                    ->where('modulo_nombre', $modulo)
                    ->update(['contenido' => json_encode($contDetalle)]);
                
                $actualizado = true;
            }

            // Mensajes de consola
            if ($actualizado) {
                $this->command->info("Actualizado en ambas tablas: Acta $cabeceraId - Modulo: $modulo");
            } else {
                $this->command->warn("No se encontró registro para: Acta $cabeceraId - Modulo: $modulo");
            }
        }

        fclose($file);
    }
}