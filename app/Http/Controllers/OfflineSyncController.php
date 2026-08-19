<?php

namespace App\Http\Controllers;

use App\Models\CabeceraMonitoreo;
use App\Models\Establecimiento;
use App\Models\MonitoreoModulos;
use App\Models\EquipoComputo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfflineSyncController extends Controller
{
    /**
     * Descarga catálogo de establecimientos para almacenamiento offline en IndexedDB.
     */
    public function descargarDatosCampo()
    {
        try {
            $establecimientos = Establecimiento::select('id', 'codigo', 'nombre', 'departamento', 'provincia', 'distrito', 'categoria', 'direccion')
                ->get();

            return response()->json([
                'success'          => true,
                'total'            => $establecimientos->count(),
                'establecimientos' => $establecimientos,
                'timestamp'        => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Error descargando datos para campo: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos para el modo offline: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesa la sincronización en lote (bulk sync) enviada desde IndexedDB al reconectarse a Internet.
     */
    public function sincronizarLoteOffline(Request $request)
    {
        try {
            DB::beginTransaction();

            $actasOffline = $request->input('actas', []);
            $usuarioId = auth()->id() ?? 1;
            $sincronizadosCount = 0;

            foreach ($actasOffline as $actaOff) {
                $estId = $actaOff['establecimiento_id'] ?? null;
                if (!$estId) continue;

                // 1. Obtener correlativo del acta de monitoreo
                $nuevoNumero = CabeceraMonitoreo::max('numero_acta') + 1;

                // 2. Crear Cabecera de Monitoreo
                $actaReal = CabeceraMonitoreo::create([
                    'establecimiento_id' => $estId,
                    'user_id'            => $usuarioId,
                    'numero_acta'        => $nuevoNumero,
                    'fecha_evaluacion'   => $actaOff['fecha'] ?? date('Y-m-d'),
                    'estado'             => 'COMPLETADO',
                    'origen'             => 'OFFLINE_PWA',
                ]);

                // 3. Procesar Consultorios Evaluados Offline
                $consultorios = $actaOff['consultorios'] ?? [];
                foreach ($consultorios as $cOff) {
                    $titulo = mb_strtoupper(trim($cOff['titulo_consultorio'] ?? 'CONSULTORIO'));
                    $slug = \Str::slug($titulo, '_') . '_' . time() . '_' . rand(100, 999);
                    $contenido = $cOff['contenido'] ?? [];
                    $contenido['titulo_consultorio'] = $titulo;

                    MonitoreoModulos::create([
                        'cabecera_monitoreo_id' => $actaReal->id,
                        'modulo_nombre'         => $slug,
                        'contenido'             => $contenido,
                    ]);

                    // 4. Crear Equipos de Cómputo inventariados offline
                    $equipos = $cOff['equipos'] ?? [];
                    foreach ($equipos as $eq) {
                        if (!empty($eq['descripcion'])) {
                            EquipoComputo::create([
                                'cabecera_monitoreo_id' => $actaReal->id,
                                'modulo'                => $slug,
                                'descripcion'           => mb_strtoupper(trim($eq['descripcion'] ?? '')),
                                'cantidad'              => (int)($eq['cantidad'] ?? 1),
                                'estado'                => mb_strtoupper(trim($eq['estado'] ?? 'OPERATIVO')),
                                'propio'                => mb_strtoupper(trim($eq['propio'] ?? 'EXCLUSIVO')),
                                'nro_serie'             => mb_strtoupper(trim($eq['nro_serie'] ?? $eq['serie'] ?? '')),
                                'observacion'           => mb_strtoupper(trim($eq['observacion'] ?? ''))
                            ]);
                        }
                    }
                }

                $sincronizadosCount++;
            }

            DB::commit();

            return response()->json([
                'success'               => true,
                'sincronizados'         => $sincronizadosCount,
                'message'               => "¡Se sincronizaron exitosamente {$sincronizadosCount} actas creadas en modo offline!"
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error sincronizando lote offline: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar sincronización: ' . $e->getMessage()
            ], 500);
        }
    }
}
