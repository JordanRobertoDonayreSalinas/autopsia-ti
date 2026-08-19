<?php

namespace App\Http\Controllers;

use App\Models\CabeceraMonitoreo;
use App\Models\Establecimiento;
use App\Models\MonitoreoEquipo;
use App\Models\MonitoreoModulos;
use App\Models\EquipoComputo;
use App\Models\Reunion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OfflineSyncController extends Controller
{
    /**
     * Espejo exacto de MonitoreoController::esEspecializada() — el
     * 'tipo_origen' determina la serie de numeración correlativa
     * (numero_acta), así que se calcula siempre en el servidor a partir del
     * establecimiento real, nunca confiando en lo que mande el cliente
     * offline (antes se aceptaba un tercer valor 'ESTANDAR' que ni siquiera
     * existe en el resto del sistema y generaba su propia serie separada).
     */
    private function esEspecializada(Establecimiento $establecimiento): bool
    {
        $codigosCSMC = ['25933', '28653', '27197', '34021', '25977', '33478', '27199', '30478'];
        $nombresCSMC = [
            'CSMC TUPAC AMARU', 'CSMC COLOR ESPERANZA', 'CSMC DECÍDETE A SER FELIZ',
            'CSMC SANTISIMA VIRGEN DE YAUCA', 'CSMC VITALIZA', 'CSMC CRISTO MORENO DE LUREN',
            'CSMC NUEVO HORIZONTE', 'CSMC MENTE SANA',
        ];

        return in_array($establecimiento->codigo, $codigosCSMC, true) ||
            in_array(strtoupper(trim($establecimiento->nombre)), $nombresCSMC, true);
    }

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

                // 1. Obtener correlativo del acta de monitoreo (numeración independiente por tipo_origen, igual que MonitoreoController::store)
                $establecimientoActa = Establecimiento::findOrFail($estId);
                $tipoOrigen = $this->esEspecializada($establecimientoActa) ? 'ESPECIALIZADA' : 'NO ESPECIALIZADA';
                $nuevoNumero = CabeceraMonitoreo::where('tipo_origen', $tipoOrigen)->lockForUpdate()->max('numero_acta') + 1;

                $responsable = mb_strtoupper(trim($actaOff['responsable'] ?? 'NO ESPECIFICADO'), 'UTF-8');
                $categoria = !empty($actaOff['categoria']) ? mb_strtoupper(trim($actaOff['categoria']), 'UTF-8') : $establecimientoActa->categoria;

                // Espejo del side-effect de MonitoreoController::store(): el jefe/categoría
                // capturados en campo actualizan también el maestro del establecimiento.
                $establecimientoActa->update(['responsable' => $responsable, 'categoria' => $categoria]);

                // 2. Crear Cabecera de Monitoreo
                // NOTA: las claves deben coincidir exactamente con el $fillable de CabeceraMonitoreo.
                // 'fecha_evaluacion', 'estado' y 'origen' no existen como columnas: Eloquent las
                // descartaba en silencio por protección de asignación masiva. 'responsable' e
                // 'implementador' son NOT NULL sin default: deben venir del payload. Antes también
                // se perdían en silencio 'categoria_congelada', pozo_tierra/panel_solar y las fotos:
                // el formulario offline sí las capturaba pero nunca viajaban en este create().
                $pozoTierra = $actaOff['pozo_tierra'] ?? 'NO';
                $panelSolar = $actaOff['panel_solar'] ?? 'NO';
                $actaReal = CabeceraMonitoreo::create([
                    'establecimiento_id' => $estId,
                    'user_id'            => $usuarioId,
                    'numero_acta'        => $nuevoNumero,
                    'tipo_origen'        => $tipoOrigen,
                    'fecha'              => $actaOff['fecha'] ?? date('Y-m-d'),
                    'responsable'        => $responsable,
                    'implementador'      => mb_strtoupper(trim($actaOff['implementador'] ?? $responsable), 'UTF-8'),
                    'categoria_congelada'      => $categoria,
                    'pozo_tierra'              => $pozoTierra,
                    'pozo_tierra_cantidad'     => $pozoTierra === 'SI' ? ($actaOff['pozo_tierra_cantidad'] ?? null) : null,
                    'pozo_tierra_operativos'   => $pozoTierra === 'SI' ? ($actaOff['pozo_tierra_operativos'] ?? null) : null,
                    'pozo_tierra_inoperativos' => $pozoTierra === 'SI' ? ($actaOff['pozo_tierra_inoperativos'] ?? null) : null,
                    'panel_solar'              => $panelSolar,
                    'panel_solar_cantidad'     => $panelSolar === 'SI' ? ($actaOff['panel_solar_cantidad'] ?? null) : null,
                    'panel_solar_operativos'   => $panelSolar === 'SI' ? ($actaOff['panel_solar_operativos'] ?? null) : null,
                    'panel_solar_inoperativos' => $panelSolar === 'SI' ? ($actaOff['panel_solar_inoperativos'] ?? null) : null,
                ]);

                // Fotos de evidencia (base64, igual que el resto del sync offline).
                $fotoUpdates = [];
                foreach (['foto1' => 'foto_1_base64', 'foto2' => 'foto_2_base64'] as $campo => $claveB64) {
                    if (!empty($actaOff[$claveB64])) {
                        $bytes = base64_decode($actaOff[$claveB64]);
                        $nombreArchivo = 'evidencias/' . Str::random(20) . '.jpg';
                        Storage::disk('public')->put($nombreArchivo, $bytes);
                        $fotoUpdates[$campo] = $nombreArchivo;
                    }
                }
                if (!empty($fotoUpdates)) {
                    $actaReal->update($fotoUpdates);
                }

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

    /**
     * Sincronización en lote de actas de reunión capturadas offline.
     *
     * Mismo patrón que sincronizarLoteOffline: cada reunión en su propia
     * sub-transacción, y solo se marca sincronizado en el dispositivo lo
     * que el servidor confirma con su offline_id -> id real.
     *
     * Las fotos viajan como base64 (foto_1_base64/foto_2_base64) en vez de
     * multipart, porque este endpoint procesa un lote JSON de N reuniones en
     * una sola petición — igual que el resto de /v1/sync.
     */
    public function sincronizarReunionesOffline(Request $request)
    {
        $reunionesOffline = $request->input('reuniones', []);

        $sincronizadas = [];
        $errores = [];

        foreach ($reunionesOffline as $rOff) {
            $offlineId = $rOff['offline_id'] ?? null;

            if (empty($rOff['titulo_reunion']) || empty($rOff['fecha_reunion'])) {
                $errores[] = ['offline_id' => $offlineId, 'message' => 'Faltan campos obligatorios (título/fecha)'];
                continue;
            }

            try {
                DB::beginTransaction();

                $participantes = $rOff['participantes'] ?? [];
                foreach ($participantes as &$p) {
                    if (isset($p['apellidos'])) $p['apellidos'] = mb_strtoupper($p['apellidos'], 'UTF-8');
                    if (isset($p['nombres'])) $p['nombres'] = mb_strtoupper($p['nombres'], 'UTF-8');
                    if (isset($p['cargo'])) $p['cargo'] = mb_strtoupper($p['cargo'], 'UTF-8');
                    if (isset($p['institucion'])) $p['institucion'] = mb_strtoupper($p['institucion'], 'UTF-8');
                }
                unset($p);

                $reunion = Reunion::create([
                    'titulo_reunion' => mb_strtoupper($rOff['titulo_reunion'], 'UTF-8'),
                    'fecha_reunion' => $rOff['fecha_reunion'],
                    'hora_reunion' => $rOff['hora_reunion'] ?? '00:00',
                    'hora_finalizada_reunion' => $rOff['hora_finalizada_reunion'] ?? null,
                    'nombre_institucion' => mb_strtoupper($rOff['nombre_institucion'] ?? '', 'UTF-8'),
                    'descripcion_general' => $rOff['descripcion_general'] ?? '',
                    'acuerdos' => $rOff['acuerdos'] ?? [],
                    'comentarios_observaciones' => $rOff['comentarios_observaciones'] ?? [],
                    'participantes' => $participantes,
                    'anulado' => false,
                ]);

                $updates = [];
                foreach (['foto_1', 'foto_2'] as $campo) {
                    $b64 = $rOff[$campo . '_base64'] ?? null;
                    if (!empty($b64)) {
                        $bytes = base64_decode($b64);
                        $nombreArchivo = 'reuniones/' . Str::random(20) . '.jpg';
                        Storage::disk('public')->put($nombreArchivo, $bytes);
                        $updates[$campo] = 'storage/' . $nombreArchivo;
                    }
                }
                if (!empty($updates)) {
                    $reunion->update($updates);
                }

                DB::commit();

                $sincronizadas[] = ['offline_id' => $offlineId, 'id' => $reunion->id];
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Error sincronizando reunión offline ({$offlineId}): " . $e->getMessage());
                $errores[] = ['offline_id' => $offlineId, 'message' => $e->getMessage()];
            }
        }

        return response()->json([
            'success' => count($errores) === 0,
            'sincronizados' => count($sincronizadas),
            'reuniones' => $sincronizadas,
            'errores' => $errores,
        ]);
    }
}
