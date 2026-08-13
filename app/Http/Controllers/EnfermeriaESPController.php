<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CabeceraMonitoreo;
use App\Models\MonitoreoModulos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Profesional;
use stdClass;

class EnfermeriaESPController extends Controller
{
    /**
     * Helper para obtener la ruta de la foto de forma segura.
     * Busca en la estructura nueva y en la antigua.
     */
    private function getFotoPath($data)
    {
        // 1. Intentar recuperar de la estructura NUEVA (anidada)
        if (!empty($data['comentarios_y_evidencias']['foto_evidencia'][0])) {
            return $data['comentarios_y_evidencias']['foto_evidencia'][0];
        }

        // 2. Intentar recuperar de la estructura ANTIGUA (raíz)
        if (!empty($data['foto_evidencia'][0])) {
            return $data['foto_evidencia'][0];
        }

        return null; // No hay foto
    }

    public function index($id) 
    {
        // 1. Validar Cabecera
        $acta = CabeceraMonitoreo::with('establecimiento')->findOrFail($id);
        
        if ($acta->tipo_origen !== 'ESPECIALIZADA') {
            return redirect()->route('usuario.monitoreo.modulos', $id)
                ->with('error', 'Módulo incorrecto.');
        }

        // 2. Recuperar el registro
        $registro = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
                                    ->where('modulo_nombre', 'sm_enfermeria')
                                    ->first();

        // Obtener contenido de forma segura
        $data = $registro ? $registro->contenido : [];
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        $data = $data ?? [];

        // 3. MAPEO DE DATOS (JSON -> VISTA)
        $dataMap = new stdClass();
        
        // A. Consultorio
        $detConsultorio = $data['detalle_del_consultorio'] ?? [];
        $dataMap->contenido = [
            'fecha'                 => $detConsultorio['fecha_monitoreo'] ?? ($data['fecha_registro'] ?? date('Y-m-d')),
            'turno'                 => $detConsultorio['turno'] ?? ($data['turno'] ?? null),
            'num_ambientes'         => $detConsultorio['num_consultorios'] ?? ($data['num_consultorios'] ?? null),
            'denominacion_ambiente' => $detConsultorio['denominacion'] ?? ($data['denominacion_consultorio'] ?? null),
        ];

        // B. DNI
        $detDni = $data['detalle_de_dni_y_firma_digital'] ?? ($data['seccion_dni'] ?? []);
        $dataMap->contenido['tipo_dni_fisico']  = $detDni['tipo_dni'] ?? null;
        $dataMap->contenido['dnie_version']     = $detDni['version_dnie'] ?? null;
        $dataMap->contenido['dnie_firma_sihce'] = $detDni['firma_digital_sihce'] ?? ($detDni['firma_sihce'] ?? null);
        $dataMap->contenido['dni_observacion']  = $detDni['observaciones_dni'] ?? ($detDni['comentarios'] ?? null);

        // --- C. SOPORTE (Lógica Idéntica a TRIAJE) ---
        // Definimos el grupo de soporte buscando en 'soporte' (nuevo) o 'dificultades' (viejo)
        $grupoSoporte = $data['soporte'] ?? ($data['dificultades'] ?? []);
        
        // Mapeamos exactamente como lo hace Triaje
        $dataMap->contenido['dificultades'] = [
            'comunica' => $grupoSoporte['inst_a_quien_comunica'] ?? ($grupoSoporte['comunica'] ?? ''),
            'medio'    => $grupoSoporte['medio_que_utiliza']     ?? ($grupoSoporte['medio'] ?? '')
        ];

        // Adicional: Inyectamos las propiedades directas por si el componente las busca así (backup de compatibilidad)
        $dataMap->dificultad_comunica_a = $dataMap->contenido['dificultades']['comunica'];
        $dataMap->dificultad_medio_uso  = $dataMap->contenido['dificultades']['medio'];
        // ---------------------------------------------

        // D. Profesional
        $profData = $data['datos_del_profesional'] ?? ($data['profesional'] ?? []);
        $docAdmin = $data['documentacion_administrativa'] ?? ($data['profesional'] ?? []); 

        $profTemp = $profData;
        $profTemp['cuenta_sihce']           = $docAdmin['utiliza_sihce'] ?? ($docAdmin['cuenta_sihce'] ?? ''); 
        $profTemp['firmo_dj']               = $docAdmin['firmo_dj'] ?? ($data['firmo_dj'] ?? '');
        $profTemp['firmo_confidencialidad'] = $docAdmin['firmo_confidencialidad'] ?? ($data['firmo_confidencialidad'] ?? '');
        
        $dataMap->contenido['profesional'] = $profTemp;

        // E. Variables sueltas
        $comEvidencia = $data['comentarios_y_evidencias'] ?? [];
        $dataMap->comentario_esp = $comEvidencia['comentarios'] ?? ($data['comentarios_generales'] ?? ($data['comentarios'] ?? null));
        $dataMap->foto_url_esp = $this->getFotoPath($data);

        // F. Capacitación
        $detCap = $data['detalles_de_capacitacion'] ?? [];
        $valCapacitacion = [
            'recibieron_cap'  => $detCap['recibio_capacitacion'] ?? ($data['recibio_capacitacion'] ?? 'NO'),
            'institucion_cap' => $detCap['inst_que_lo_capacito'] ?? ($data['inst_capacitacion'] ?? null)
        ];
        
        // G. Inventario
        $rawInventario = $data['equipos_de_computo'] ?? ($data['inventario'] ?? []);
        $valInventario = [];
        
        foreach($rawInventario as $item) {
            $itemArray = (array)$item; 
            $obj = new stdClass();
            $obj->descripcion = $itemArray['descripcion'] ?? '';
            $obj->cantidad    = $itemArray['cantidad'] ?? 1;
            $obj->estado      = $itemArray['estado'] ?? 'OPERATIVO';
            $obj->propio      = $itemArray['propio'] ?? ($itemArray['propiedad'] ?? 'COMPARTIDO'); 
            $obj->nro_serie   = $itemArray['nro_serie'] ?? ($itemArray['codigo'] ?? ''); 
            $obj->observacion = $itemArray['observacion'] ?? '';
            $valInventario[] = $obj;
        }

        return view('usuario.monitoreo.modulos_especializados.enfermeria', compact(
            'acta', 'dataMap', 'valCapacitacion', 'valInventario'
        ));
    }

    public function store(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // 1. OBTENER DATOS PREVIOS (Para gestión de foto)
            $registroPrevio = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
                                              ->where('modulo_nombre', 'sm_enfermeria')
                                              ->first();
            
            $contenidoPrevio = $registroPrevio ? (is_string($registroPrevio->contenido) ? json_decode($registroPrevio->contenido, true) : $registroPrevio->contenido) : [];

            // 2. GESTIÓN DE FOTO
            $rutaFotoAnterior = $this->getFotoPath($contenidoPrevio ?? []);
            $rutaFotoFinal = $rutaFotoAnterior;

            if ($request->hasFile('foto_esp_file')) {
                // Borrar anterior si existe
                if ($rutaFotoAnterior && Storage::disk('public')->exists($rutaFotoAnterior)) {
                    Storage::disk('public')->delete($rutaFotoAnterior);
                }
                // Guardar nueva
                $rutaFotoFinal = $request->file('foto_esp_file')->store('evidencias_esp', 'public');
            }
            $arrayFotos = $rutaFotoFinal ? [$rutaFotoFinal] : [];

            // 3. CAPTURA DE INPUTS
            $rawCont = $request->input('contenido', []);
            $rawProf = $request->input('contenido.profesional', []);
            $rawCap  = $request->input('capacitacion', []);
            $rawEquipos = $request->input('equipos', []);
            $comentario = $request->input('comentario_esp');

            // 4. ACTUALIZACIÓN MAESTRO PROFESIONALES
            if (!empty($rawProf['doc'])) {
                Profesional::updateOrCreate(
                    ['doc' => trim($rawProf['doc'])],
                    [
                        'tipo_doc'         => $rawProf['tipo_doc'] ?? 'DNI',
                        'nombres'          => mb_strtoupper($rawProf['nombres'] ?? '', 'UTF-8'),
                        'apellido_paterno' => mb_strtoupper($rawProf['apellido_paterno'] ?? '', 'UTF-8'),
                        'apellido_materno' => mb_strtoupper($rawProf['apellido_materno'] ?? '', 'UTF-8'),
                        'email'            => strtolower($rawProf['email'] ?? ''),
                        'telefono'         => $rawProf['telefono'] ?? null,
                        'cargo'            => mb_strtoupper($rawProf['cargo'] ?? '', 'UTF-8'),
                    ]
                );
            }

            // 5. CONSTRUCCIÓN DEL JSON (ESTRUCTURA NUEVA ESTANDARIZADA)
            $jsonToSave = [];

            // 5.1 Consultorio
            $jsonToSave['detalle_del_consultorio'] = [
                'fecha_monitoreo'  => $rawCont['fecha'] ?? date('Y-m-d'),
                'turno'            => $rawCont['turno'] ?? '',
                'num_consultorios' => $rawCont['num_ambientes'] ?? '',
                'denominacion'     => mb_strtoupper($rawCont['denominacion_ambiente'] ?? '', 'UTF-8'),
            ];

            // 5.2 Profesional
            $jsonToSave['datos_del_profesional'] = [
                'doc'              => $rawProf['doc'] ?? '',
                'tipo_doc'         => $rawProf['tipo_doc'] ?? 'DNI',
                'nombres'          => mb_strtoupper($rawProf['nombres'] ?? '', 'UTF-8'),
                'apellido_paterno' => mb_strtoupper($rawProf['apellido_paterno'] ?? '', 'UTF-8'),
                'apellido_materno' => mb_strtoupper($rawProf['apellido_materno'] ?? '', 'UTF-8'),
                'email'            => strtolower($rawProf['email'] ?? ''),
                'telefono'         => $rawProf['telefono'] ?? '',
                'cargo'            => mb_strtoupper($rawProf['cargo'] ?? '', 'UTF-8'),
            ];

            // 5.3 Documentación
            $jsonToSave['documentacion_administrativa'] = [
                'utiliza_sihce'          => $rawProf['cuenta_sihce'] ?? 'NO',
                'firmo_dj'               => $rawProf['firmo_dj'] ?? 'NO',
                'firmo_confidencialidad' => $rawProf['firmo_confidencialidad'] ?? 'NO',
            ];

            // 5.4 DNI
            $jsonToSave['detalle_de_dni_y_firma_digital'] = [
                'tipo_dni'            => $rawCont['tipo_dni_fisico'] ?? '',
                'version_dnie'        => $rawCont['dnie_version'] ?? '',
                'firma_digital_sihce' => $rawCont['dnie_firma_sihce'] ?? '',
                'observaciones_dni'   => mb_strtoupper($rawCont['dni_observacion'] ?? '', 'UTF-8'),
            ];

            // 5.5 Capacitación
            $jsonToSave['detalles_de_capacitacion'] = [
                'recibio_capacitacion' => $rawCap['recibieron_cap'] ?? 'NO',
                'inst_que_lo_capacito' => mb_strtoupper($rawCap['institucion_cap'] ?? '', 'UTF-8'),
            ];

            // 5.6 Soporte
            $dificultades = $rawCont['dificultades'] ?? [];
            $jsonToSave['soporte'] = [
                'inst_a_quien_comunica' => mb_strtoupper($dificultades['comunica'] ?? '', 'UTF-8'),
                'medio_que_utiliza'     => mb_strtoupper($dificultades['medio'] ?? '', 'UTF-8'),
            ];

            // 5.7 Equipos (Inventario)
            $equiposMapeados = [];
            if (is_array($rawEquipos)) {
                foreach($rawEquipos as $item) {
                    if (!empty($item['descripcion'])) {
                        $equiposMapeados[] = [
                            'descripcion' => mb_strtoupper($item['descripcion'], 'UTF-8'),
                            'cantidad'    => $item['cantidad'] ?? '1',
                            'estado'      => $item['estado'] ?? 'OPERATIVO',
                            'propio'      => $item['propio'] ?? 'COMPARTIDO',
                            'nro_serie'   => mb_strtoupper($item['nro_serie'] ?? '', 'UTF-8'),
                            'observacion' => mb_strtoupper($item['observacion'] ?? '', 'UTF-8'),
                        ];
                    }
                }
            }
            $jsonToSave['equipos_de_computo'] = $equiposMapeados;

            // 5.8 Comentarios y Fotos
            $jsonToSave['comentarios_y_evidencias'] = [
                'comentarios'    => mb_strtoupper($comentario ?? '', 'UTF-8'),
                'foto_evidencia' => $arrayFotos
            ];

            // 6. GUARDADO FINAL
            MonitoreoModulos::updateOrCreate(
                ['cabecera_monitoreo_id' => $id, 'modulo_nombre' => 'sm_enfermeria'],
                [
                    'contenido'        => $jsonToSave,
                    'pdf_firmado_path' => null
                ]
            );

            DB::commit();

            // REDIRECT ESPECÍFICO DE SUBMÓDULO
            return redirect()
                ->route('usuario.monitoreo.salud_mental_group.index', $id)
                ->with('success', 'Módulo Enfermería guardado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }
}