<?php

namespace App\Http\Controllers;

use App\Models\CabeceraMonitoreo;
use App\Models\Establecimiento;
use App\Models\MonitoreoEquipo;
use App\Models\MonitoreoModulos;
use App\Models\EquipoComputo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfflineSyncController extends Controller
{
    /**
     * Devuelve la versión actual del sistema (v1.2.0) y la marca de tiempo del catálogo para Flutter/Apps.
     */
    public function apiVersion()
    {
        try {
            $maxUpdated = Establecimiento::max('updated_at');
            $catalogTimestamp = $maxUpdated ? strtotime($maxUpdated) : time();

            return response()->json([
                'success'          => true,
                'app'              => 'Autopsia TI',
                'system_version'   => '1.2.0',
                'catalog_version'  => $catalogTimestamp,
                'total_ipress'     => Establecimiento::count(),
                'min_app_version'  => '1.0.0',
                'timestamp'        => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener versión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descarga catálogo de establecimientos para almacenamiento offline en SQLite.
     *
     * Se seleccionan todas las columnas del espejo local (incluyendo latitud/longitud,
     * red/microred y responsable) para que el catálogo offline no sea una versión
     * recortada del establecimiento real — antes solo traía 8 de las 34 columnas y el
     * mapa offline no tenía dónde guardar las coordenadas.
     */
    public function descargarDatosCampo()
    {
        try {
            $establecimientos = Establecimiento::select([
                'id', 'codigo', 'nombre', 'institucion', 'direccion', 'departamento',
                'provincia', 'distrito', 'centro_poblado', 'telefono', 'correo',
                'red', 'microred', 'clas', 'odsis', 'responsable',
                'tipo_documento', 'numero_documento', 'colegio_profesional', 'colegiatura', 'rne',
                'categoria', 'estado', 'condicion', 'latitud', 'longitud', 'altitud',
                'fecha_creacion_resolucion', 'fecha_registro', 'numero_resolucion_creacion',
                'horario_atencion', 'numero_ambientes', 'numero_camas', 'upss', 'ups',
            ])->get();

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
     * Procesa la sincronización en lote (bulk sync) enviada desde Flutter al reconectarse a Internet.
     *
     * Cada acta se procesa en su propia sub-transacción: si una falla (p. ej. datos
     * incompletos de un acta puntual), las demás igual se sincronizan — antes un solo
     * error tumbaba el lote completo por el DB::beginTransaction() global.
     *
     * La respuesta incluye 'actas' con el mapeo offline_id -> id/numero_acta real,
     * para que el cliente solo marque como sincronizado lo que el servidor confirmó
     * (antes no había forma de saber qué id le tocó a cada acta local).
     */
    public function sincronizarLoteOffline(Request $request)
    {
        $actasOffline = $request->input('actas', []);
        $usuarioId = auth()->id() ?? 1;

        $sincronizadas = [];
        $errores = [];

        foreach ($actasOffline as $actaOff) {
            $offlineId = $actaOff['offline_id'] ?? null;
            $estId = $actaOff['establecimiento_id'] ?? null;

            if (!$estId) {
                $errores[] = ['offline_id' => $offlineId, 'message' => 'Falta establecimiento_id'];
                continue;
            }

            try {
                DB::beginTransaction();

                // 1. Obtener correlativo del acta de monitoreo (numeración independiente por tipo_origen, igual que MonitoreoController)
                $tipoOrigen = $actaOff['tipo_origen'] ?? 'ESTANDAR';
                $nuevoNumero = CabeceraMonitoreo::where('tipo_origen', $tipoOrigen)->lockForUpdate()->max('numero_acta') + 1;

                // 2. Crear Cabecera de Monitoreo
                // NOTA: las claves deben coincidir exactamente con el $fillable de CabeceraMonitoreo.
                // 'fecha_evaluacion', 'estado' y 'origen' no existen como columnas: Eloquent las
                // descartaba en silencio por protección de asignación masiva. 'responsable' e
                // 'implementador' son NOT NULL sin default: deben venir del payload.
                $actaReal = CabeceraMonitoreo::create([
                    'establecimiento_id' => $estId,
                    'user_id'            => $usuarioId,
                    'numero_acta'        => $nuevoNumero,
                    'tipo_origen'        => $tipoOrigen,
                    'fecha'              => $actaOff['fecha'] ?? date('Y-m-d'),
                    'responsable'        => $actaOff['responsable'] ?? 'NO ESPECIFICADO',
                    'implementador'      => $actaOff['implementador'] ?? ($actaOff['responsable'] ?? 'NO ESPECIFICADO'),
                ]);

                // 3. Personal del establecimiento presente en la visita
                $equipoMonitoreo = $actaOff['equipo_monitoreo'] ?? [];
                foreach ($equipoMonitoreo as $persona) {
                    if (empty($persona['doc'])) continue;
                    MonitoreoEquipo::create([
                        'cabecera_monitoreo_id' => $actaReal->id,
                        'tipo_doc'              => $persona['tipo_doc'] ?? 'DNI',
                        'doc'                   => $persona['doc'],
                        'apellido_paterno'      => $persona['apellido_paterno'] ?? null,
                        'apellido_materno'      => $persona['apellido_materno'] ?? null,
                        'nombres'               => $persona['nombres'] ?? null,
                        'cargo'                 => $persona['cargo'] ?? 'Implementador',
                        'institucion'           => $persona['institucion'] ?? '',
                    ]);
                }

                // 4. Procesar Consultorios/Módulos Evaluados Offline
                //
                // Los módulos fijos (ej. 'rrhh') tienen una pantalla propia en el sistema
                // (RecursosHumanosController) que busca la fila por modulo_nombre EXACTO.
                // Si se le asignara un slug aleatorio como a los consultorios ad-hoc, esa
                // pantalla nunca encontraría los datos capturados en campo.
                $modulosFijos = ['rrhh', 'infraestructura_2d'];

                $consultorios = $actaOff['consultorios'] ?? [];
                foreach ($consultorios as $cOff) {
                    $tituloRaw = trim($cOff['titulo_consultorio'] ?? 'CONSULTORIO');
                    $esModuloFijo = in_array(mb_strtolower($tituloRaw), $modulosFijos, true);

                    $contenido = $cOff['contenido'] ?? [];
                    if (is_string($contenido)) {
                        $contenido = json_decode($contenido, true) ?? [];
                    }

                    if ($esModuloFijo) {
                        $slug = mb_strtolower($tituloRaw);
                    } else {
                        $titulo = mb_strtoupper($tituloRaw);
                        $slug = \Str::slug($titulo, '_') . '_' . time() . '_' . rand(100, 999);
                        $contenido['titulo_consultorio'] = $titulo;
                    }

                    // updateOrCreate: si el módulo fijo ya se sincronizó antes (ej. el
                    // auditor volvió a entrar a RR.HH. y agregó más personal), se
                    // actualiza el mismo registro en vez de duplicarlo.
                    MonitoreoModulos::updateOrCreate(
                        ['cabecera_monitoreo_id' => $actaReal->id, 'modulo_nombre' => $slug],
                        ['contenido' => $contenido]
                    );

                    // 5. Crear Equipos de Cómputo inventariados offline
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
                                'observacion'           => mb_strtoupper(trim($eq['observacion'] ?? '')),
                                'especificaciones'      => $eq['especificaciones'] ?? null,
                            ]);
                        }
                    }
                }

                DB::commit();

                $sincronizadas[] = [
                    'offline_id'  => $offlineId,
                    'id'          => $actaReal->id,
                    'numero_acta' => $actaReal->numero_acta,
                ];
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Error sincronizando acta offline ({$offlineId}): " . $e->getMessage());
                $errores[] = ['offline_id' => $offlineId, 'message' => $e->getMessage()];
            }
        }

        return response()->json([
            'success'       => count($errores) === 0,
            'sincronizados' => count($sincronizadas),
            'actas'         => $sincronizadas,
            'errores'       => $errores,
            'message'       => count($errores) === 0
                ? '¡Se sincronizaron exitosamente ' . count($sincronizadas) . ' actas creadas en modo offline!'
                : count($sincronizadas) . ' acta(s) sincronizada(s), ' . count($errores) . ' con error.',
        ]);
    }
}
