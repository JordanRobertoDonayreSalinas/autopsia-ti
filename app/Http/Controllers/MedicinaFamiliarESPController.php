<?php

namespace App\Http\Controllers;

use App\Models\CabeceraMonitoreo;
use App\Models\EquipoComputo;
use App\Models\MonitoreoModulos;
use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MedicinaFamiliarESPController extends Controller
{
    public function index($id)
    {
        $monitoreo = CabeceraMonitoreo::with('establecimiento')->findOrFail($id);

        if ($monitoreo->tipo_origen !== 'ESPECIALIZADA') {
            return redirect()->route('usuario.monitoreo.modulos', $id)
                ->with('error', 'Este módulo no corresponde al tipo de establecimiento.');
        }

        // Recuperar equipos
        $equipos = EquipoComputo::where('cabecera_monitoreo_id', $id)
            ->where('modulo', 'sm_med_familiar')
            ->get();

        $detalle = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', 'sm_med_familiar')
            ->first();

        // Si no existe, creamos una instancia vacía
        if (! $detalle) {
            $detalle = new MonitoreoModulos;
            $detalle->contenido = [];
        }

        // -------------------------------------------------------------------------
        // TRANSFORMACIÓN DE DATOS (BD -> VISTA)
        // Convertimos la estructura jerárquica (JSON nuevo) a plana para que la vista la entienda
        // -------------------------------------------------------------------------
        if ($detalle && ! empty($detalle->contenido)) {
            $dbData = $detalle->contenido;

            $defaults = [
                'soporte' => ['inst_a_quien_comunica' => null, 'medio_que_utiliza' => null],
                'datos_del_profesional' => [],
                'detalle_de_dni_y_firma_digital' => [],
                'detalles_de_capacitacion' => [],
                'detalle_del_consultorio' => [],
            ];

            $viewData = array_replace_recursive($defaults, $dbData);

            // 1. Datos Generales del Consultorio
            $consultorio = $dbData['detalle_del_consultorio'] ?? [];
            $viewData['fecha'] = $consultorio['fecha_monitoreo'] ?? null;
            $viewData['turno'] = $consultorio['turno'] ?? null;
            // Mapeo correcto para que el componente esp_1_detalleDeConsultorio reciba los valores
            $viewData['num_ambientes'] = $consultorio['num_consultorios'] ?? null;
            $viewData['denominacion_ambiente'] = $consultorio['denominacion'] ?? null;

            // 2. Profesional (Array directo)
            $profesional = $dbData['datos_del_profesional'] ?? [];
            $viewData['profesional'] = $profesional;

            // 3. Documentación Administrativa (Fusión con profesional para compatibilidad)
            $docAdmin = $dbData['documentacion_administrativa'] ?? [];
            $viewData['profesional']['cuenta_sihce'] = $docAdmin['utiliza_sihce'] ?? null;
            $viewData['profesional']['firmo_dj'] = $docAdmin['firmo_dj'] ?? null;
            $viewData['profesional']['firmo_confidencialidad'] = $docAdmin['firmo_confidencialidad'] ?? null;

            // 4. Detalle DNI (Aplanamos para los inputs)
            $dni = $dbData['detalle_de_dni_y_firma_digital'] ?? [];
            $viewData['tipo_dni_fisico'] = $dni['tipo_dni'] ?? null;
            $viewData['dnie_version'] = $dni['version_dnie'] ?? null;
            $viewData['dnie_firma_sihce'] = $dni['firma_digital_sihce'] ?? null;
            $viewData['dni_observacion'] = $dni['observaciones_dni'] ?? null;

            // 5. Capacitación (Adaptador para Componente 4)
            $cap = $dbData['detalles_de_capacitacion'] ?? [];
            $viewData['recibio_capacitacion'] = $cap['recibio_capacitacion'] ?? null;
            $viewData['inst_capacitacion'] = $cap['inst_que_lo_capacito'] ?? null;
            // Traducción para Componente AlpineJS
            $viewData['recibieron_cap'] = $cap['recibio_capacitacion'] ?? null;
            $viewData['institucion_cap'] = $cap['inst_que_lo_capacito'] ?? null;

            // 6. Soporte (Adaptador para Componente 6)
            $soporte = $dbData['soporte'] ?? [];
            $viewData['dificultades'] = [
                'comunica' => $soporte['inst_a_quien_comunica'] ?? null,
                'medio' => $soporte['medio_que_utiliza'] ?? null,
            ];
            // Inyección de propiedades directas para Componente 6
            $detalle->dificultad_comunica_a = $soporte['inst_a_quien_comunica'] ?? null;
            $detalle->dificultad_medio_uso = $soporte['medio_que_utiliza'] ?? null;

            // 7. Evidencia y Comentarios
            $comentarios = $dbData['comentarios_y_evidencias'] ?? [];
            $viewData['comentario_esp'] = $comentarios['comentarios'] ?? null;
            $viewData['foto_evidencia'] = $comentarios['foto_evidencia'] ?? [];

            // Traducción para Componente 7 (Foto única visual)
            $evidencia = $viewData['foto_evidencia'];
            $viewData['foto_url_esp'] = is_array($evidencia) ? ($evidencia[0] ?? null) : $evidencia;

            // Asignamos la data aplanada a memoria para la vista
            $detalle->contenido = $viewData;
        }

        $data = is_array($detalle->contenido) ? $detalle->contenido : [];

        return view('usuario.monitoreo.modulos_especializados.medicina_familiar', compact('monitoreo', 'data', 'equipos', 'detalle'));
    }

    public function store(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $monitoreo = CabeceraMonitoreo::findOrFail($id);
            $modulo = 'sm_med_familiar';

            // 1. RECIBIMOS LOS DATOS (Estructura Plana del Formulario)
            $input = $request->input('contenido', []);

            // ---------------------------------------------------------
            // FUSIÓN DE COMPONENTES EXTERNOS (Capacitación y Dificultades)
            // ---------------------------------------------------------
            // Componente 4 (Capacitación)
            if ($request->has('capacitacion')) {
                $cap = $request->input('capacitacion');
                $input['recibio_capacitacion'] = $cap['recibieron_cap'] ?? null;
                $input['inst_capacitacion'] = $cap['institucion_cap'] ?? null;
            }

            // Componente 6 (Dificultades)
            if ($request->has('dificultades')) {
                $input['dificultades'] = $request->input('dificultades');
            }

            // ---------------------------------------------------------
            // REGLAS DE NEGOCIO (Limpieza de Datos)
            // ---------------------------------------------------------

            // Regla SIHCE = NO
            $usaSihce = $input['profesional']['cuenta_sihce'] ?? 'NO';
            if ($usaSihce === 'NO') {
                $input['recibio_capacitacion'] = null;
                $input['inst_capacitacion'] = null;
                $input['dificultades'] = ['comunica' => null, 'medio' => null];
                $input['profesional']['firmo_dj'] = null;
                $input['profesional']['firmo_confidencialidad'] = null;
            }

            // Regla NO Capacitación
            if (($input['recibio_capacitacion'] ?? '') === 'NO') {
                $input['inst_capacitacion'] = null;
            }

            // Regla Documento != DNI
            $tipoDoc = $input['profesional']['tipo_doc'] ?? '';
            if ($tipoDoc !== 'DNI') {
                $input['tipo_dni_fisico'] = null;
                $input['dnie_version'] = null;
                $input['dnie_firma_sihce'] = null;
                $input['dni_observacion'] = null;
            }

            // Regla DNI Azul
            if (($input['tipo_dni_fisico'] ?? '') === 'AZUL') {
                $input['dnie_version'] = null;
                $input['dnie_firma_sihce'] = null;
            }

            // ---------------------------------------------------------
            // CONSTRUCCIÓN DE LA ESTRUCTURA JERÁRQUICA (JSON ESTRUCTURADO)
            // ---------------------------------------------------------

            // Preparar equipos de cómputo
            $equiposComputo = [];
            if ($request->has('equipos') && is_array($request->equipos)) {
                foreach ($request->equipos as $eq) {
                    if (! empty($eq['descripcion'])) {
                        $equiposComputo[] = [
                            'descripcion' => mb_strtoupper(trim($eq['descripcion'])),
                            'cantidad' => (string) ((int) ($eq['cantidad'] ?? 1)),
                            'estado' => $eq['estado'] ?? 'OPERATIVO',
                            'propio' => $eq['propio'] ?? 'EXCLUSIVO',
                            'nro_serie' => isset($eq['nro_serie']) ? mb_strtoupper(trim($eq['nro_serie'])) : null,
                            'observacion' => isset($eq['observacion']) ? mb_strtoupper(trim($eq['observacion'])) : null,
                        ];
                    }
                }
            }

            $structuredData = [
                'detalle_del_consultorio' => [
                    'fecha_monitoreo' => $input['fecha'] ?? date('Y-m-d'),
                    'turno' => $input['turno'] ?? null,
                    'num_consultorios' => $input['num_ambientes'] ?? null,
                    'denominacion' => $input['denominacion_ambiente'] ?? null,
                ],

                'datos_del_profesional' => [
                    'doc' => $input['profesional']['doc'] ?? null,
                    'tipo_doc' => $input['profesional']['tipo_doc'] ?? null,
                    'nombres' => $input['profesional']['nombres'] ?? null,
                    'apellido_paterno' => $input['profesional']['apellido_paterno'] ?? null,
                    'apellido_materno' => $input['profesional']['apellido_materno'] ?? null,
                    'email' => $input['profesional']['email'] ?? null,
                    'telefono' => $input['profesional']['telefono'] ?? null,
                    'cargo' => $input['profesional']['cargo'] ?? null,
                ],

                'documentacion_administrativa' => [
                    'utiliza_sihce' => $input['profesional']['cuenta_sihce'] ?? null,
                    'firmo_dj' => $input['profesional']['firmo_dj'] ?? null,
                    'firmo_confidencialidad' => $input['profesional']['firmo_confidencialidad'] ?? null,
                ],

                'detalle_de_dni_y_firma_digital' => [
                    'tipo_dni' => $input['tipo_dni_fisico'] ?? null,
                    'version_dnie' => $input['dnie_version'] ?? null,
                    'firma_digital_sihce' => $input['dnie_firma_sihce'] ?? null,
                    'observaciones_dni' => $input['dni_observacion'] ?? null,
                ],

                'detalles_de_capacitacion' => [
                    'recibio_capacitacion' => $input['recibio_capacitacion'] ?? null,
                    'inst_que_lo_capacito' => $input['inst_capacitacion'] ?? null,
                ],

                'soporte' => [
                    'inst_a_quien_comunica' => $input['dificultades']['comunica'] ?? null,
                    'medio_que_utiliza' => $input['dificultades']['medio'] ?? null,
                ],

                'equipos_de_computo' => $equiposComputo,

                'comentarios_y_evidencias' => [
                    'comentarios' => $request->input('comentario_esp') ?? ($input['comentario_esp'] ?? null),
                    'foto_evidencia' => [], // Se llenará más abajo
                ],
            ];

            // ---------------------------------------------------------
            // NORMALIZACIÓN DE TEXTO (Mayúsculas)
            // ---------------------------------------------------------
            array_walk_recursive($structuredData, function (&$value, $key) {
                if (is_string($value)) {
                    if ($key === 'email' || str_contains($value, 'evidencias_')) {
                        return;
                    }
                    $value = mb_strtoupper(trim($value));
                }
            });

            // ---------------------------------------------------------
            // SINCRONIZACIÓN DE PROFESIONALES (Tabla Externa)
            // ---------------------------------------------------------
            if (! empty($structuredData['datos_del_profesional']['doc'])) {
                Profesional::updateOrCreate(
                    ['doc' => trim($structuredData['datos_del_profesional']['doc'])],
                    [
                        'tipo_doc' => $structuredData['datos_del_profesional']['tipo_doc'] ?? 'DNI',
                        'apellido_paterno' => $structuredData['datos_del_profesional']['apellido_paterno'],
                        'apellido_materno' => $structuredData['datos_del_profesional']['apellido_materno'],
                        'nombres' => $structuredData['datos_del_profesional']['nombres'],
                        'email' => strtolower($structuredData['datos_del_profesional']['email']),
                        'telefono' => $structuredData['datos_del_profesional']['telefono'],
                        'cargo' => $structuredData['datos_del_profesional']['cargo'],
                    ]
                );
            }

            // ---------------------------------------------------------
            // GESTIÓN DE EQUIPOS (Tabla Externa)
            // ---------------------------------------------------------
            EquipoComputo::where('cabecera_monitoreo_id', $id)->where('modulo', $modulo)->delete();
            if (! empty($equiposComputo)) {
                foreach ($equiposComputo as $eq) {
                    EquipoComputo::create([
                        'cabecera_monitoreo_id' => $id,
                        'modulo' => $modulo,
                        'descripcion' => $eq['descripcion'],
                        'cantidad' => (int) $eq['cantidad'],
                        'estado' => $eq['estado'],
                        'nro_serie' => $eq['nro_serie'] ?? null,
                        'propio' => $eq['propio'] ?? 'EXCLUSIVO',
                        'observacion' => $eq['observacion'] ?? null,
                        'especificaciones' => $eq['especificaciones'] ?? null,
                    ]);
                }
            }

            // ---------------------------------------------------------
            // GESTIÓN DE FOTOS
            // ---------------------------------------------------------
            $registroPrevio = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
                ->where('modulo_nombre', $modulo)
                ->first();
            $fotosFinales = [];

            // Si ya existía data, buscamos la foto en la estructura nueva o la antigua
            if ($registroPrevio && isset($registroPrevio->contenido['comentarios_y_evidencias']['foto_evidencia'])) {
                $prev = $registroPrevio->contenido['comentarios_y_evidencias']['foto_evidencia'];
                $fotosFinales = is_array($prev) ? $prev : [$prev];
            } elseif ($registroPrevio && isset($registroPrevio->contenido['foto_evidencia'])) {
                // Compatibilidad con estructura antigua
                $prev = $registroPrevio->contenido['foto_evidencia'];
                $fotosFinales = is_array($prev) ? $prev : [$prev];
            }

            if ($request->hasFile('foto_esp_file')) {
                // Borrar foto anterior
                if (count($fotosFinales) > 0) {
                    foreach ($fotosFinales as $pathViejo) {
                        if (Storage::disk('public')->exists($pathViejo)) {
                            Storage::disk('public')->delete($pathViejo);
                        }
                    }
                }
                $file = $request->file('foto_esp_file');
                $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                $nombreArchivo = "evidencia_acta_{$id}_medicina_familiar_".date('Ymd_His').'.'.$ext;
                $path = $file->storeAs('evidencias_monitoreo', $nombreArchivo, 'public');
                $fotosFinales = [$path];
            }
            $structuredData['comentarios_y_evidencias']['foto_evidencia'] = $fotosFinales;

            // ---------------------------------------------------------
            // GUARDAR
            // ---------------------------------------------------------
            MonitoreoModulos::updateOrCreate(
                ['cabecera_monitoreo_id' => $id, 'modulo_nombre' => $modulo],
                ['contenido' => $structuredData] // Guardamos el JSON estructurado
            );

            DB::commit();

            return redirect()->route('usuario.monitoreo.salud_mental_group.index', $id)
                ->with('success', 'Módulo Medicina Familiar ESP sincronizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error Módulo Medicina Familiar ESP (Store) - Acta {$id}: ".$e->getMessage());

            return back()->withErrors(['error' => 'Error al guardar: '.$e->getMessage()])->withInput();
        }
    }
}
