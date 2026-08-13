<?php

namespace App\Http\Controllers;

use App\Models\CabeceraMonitoreo;
use App\Models\MonitoreoModulos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Infraestructura2DController extends Controller
{
    public function index($id)
    {
        $acta = CabeceraMonitoreo::findOrFail($id);
        
        $modulo = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', 'infraestructura_2d')
            ->first();

        $contenido = $modulo ? $modulo->contenido : [
            'consultorios' => []
        ];

        // ── Cargar datos de todos los módulos activos para la sincronización ──
        $modulosData = $this->_buildModulosData($id);

        return view('usuario.monitoreo.modulos.infraestructura_2d', compact('acta', 'contenido', 'modulosData'));
    }

    /**
     * Agrega la información de todos los módulos registrados para un acta.
     * Normaliza equipos (sea de equipos_listado JSON o tabla equipo_computos)
     * a un formato uniforme para el editor 2D.
     */
    public function getSyncData($id)
    {
        $modulosData = $this->_buildModulosData($id);
        return response()->json($modulosData);
    }

    private function _buildModulosData(int $actaId): array
    {
        // Label legible por cada slug de módulo
        $labels = [
            'citas'                     => 'Citas',
            'atencion_prenatal'         => 'Prenatal',
            'consulta_medicina'         => 'Medicina',
            'odontologia'               => 'Odontología',
            'psicologia'                => 'Psicología',
            'triaje'                    => 'Triaje',
            'urgencias'                 => 'Urgencias',
            'laboratorio'               => 'Laboratorio',
            'inmunizaciones'            => 'Inmunizaciones',
            'planificacion'             => 'Planificación Fam.',
            'puerperio'                 => 'Puerperio',
            'referencias'               => 'Referencias',
            'fua_electronico'           => 'FUA Electrónico',
            'gestion_administrativa'    => 'Gestión Adm.',
            'terapia_esp'               => 'Terapia ESP',
            'medicina_familiar_esp'     => 'Med. Familiar ESP',
            'psicologia_esp'            => 'Psicología ESP',
            'psiquiatria_esp'           => 'Psiquiatría ESP',
        ];

        // Módulos que guardan equipos en la tabla equipo_computos
        $usamTablaEquipos = [
            'consulta_medicina', 'odontologia', 'psicologia', 'triaje',
            'urgencias', 'laboratorio', 'inmunizaciones', 'planificacion',
            'puerperio', 'referencias', 'fua_electronico', 'gestion_administrativa',
            'terapia_esp', 'medicina_familiar_esp', 'psicologia_esp', 'psiquiatria_esp',
        ];

        // Cargar equipos de tabla externa en un solo query
        $equiposTabla = \App\Models\EquipoComputo::where('cabecera_monitoreo_id', $actaId)
            ->get()
            ->groupBy('modulo');

        // Cargar todos los módulos registrados (A: Nuevos, B: Antiguos)
        $modulosNuevos = DB::table('mon_detalle_modulos')
            ->where('cabecera_monitoreo_id', $actaId)
            ->get()
            ->keyBy('modulo_nombre');

        $modulosAntiguos = DB::table('mon_monitoreo_modulos')
            ->where('cabecera_monitoreo_id', $actaId)
            ->get();

        $registros = collect();
        $nombres = $modulosNuevos->keys()
            ->merge($modulosAntiguos->pluck('modulo_nombre'))
            ->unique();

        foreach($nombres as $nombre) {
            if ($nombre === 'infraestructura_2d' || $nombre === 'infraestructura_3d' || $nombre === 'config_modulos') continue;
            
            if ($modulosNuevos->has($nombre)) {
                $registros->push($modulosNuevos->get($nombre));
            } else {
                $old = $modulosAntiguos->firstWhere('modulo_nombre', $nombre);
                if ($old) $registros->push($old);
            }
        }

        $result = [];

        foreach ($registros as $reg) {
            $slug    = $reg->modulo_nombre;
            $content = is_string($reg->contenido) ? json_decode($reg->contenido, true) : ($reg->contenido ?? []);

            // ── 1. Normalizar equipos ──────────────────────────────────────────
            $equipos = [];

            if (in_array($slug, $usamTablaEquipos)) {
                // Fuente: tabla equipo_computos
                $rows = $equiposTabla->get($slug, collect());
                foreach ($rows as $eq) {
                    $nombre = strtoupper(trim($eq->descripcion ?? ''));
                    if ($nombre) $equipos[] = $nombre;
                }
            } else {
                // Fuente: campo JSON equipos_listado (Citas, Prenatal, Parto…)
                $listado = $content['equipos_listado'] ?? $content['equipos'] ?? [];
                if (is_array($listado)) {
                    foreach ($listado as $eq) {
                        if (is_array($eq)) {
                            $nombre = strtoupper(trim($eq['nombre'] ?? $eq['descripcion'] ?? ''));
                        } else {
                            $nombre = strtoupper(trim((string)$eq));
                        }
                        if ($nombre) $equipos[] = $nombre;
                    }
                }
            }

            // ── 2. Extraer conectividad y SIHCE ───────────────────────────────
            $utiliza_sihce      = strtoupper(trim($content['utiliza_sihce'] ?? ''));
            $tipo_conectividad  = strtoupper(trim($content['tipo_conectividad'] ?? ''));

            // ── 3. Extraer cantidad de ambientes ──────────────────────────────
            $cantidad = (int)($content['nro_consultorios'] 
                             ?? $content['num_consultorios'] 
                             ?? $content['n_consultorios'] 
                             ?? $content['cantidad_consultorios']
                             ?? 1);
            if ($cantidad < 1) $cantidad = 1;

            // ── 4. Nombre de pantalla ─────────────────────────────────────────
            $nombreLabel = $labels[$slug] ?? ucwords(str_replace('_', ' ', $slug));

            $result[] = [
                'slug'              => $slug,
                'label'             => $nombreLabel,
                'cantidad'          => $cantidad,
                'equipos'           => array_values(array_unique($equipos)),
                'utiliza_sihce'     => $utiliza_sihce,    // 'SI' | 'NO' | ''
                'tipo_conectividad' => $tipo_conectividad, // 'WIFI' | 'CABLEADO' | 'SIN CONECTIVIDAD' | ''
            ];
        }

        return $result;
    }

    public function store(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $moduloNombre = 'infraestructura_2d';
            $contenido = $request->contenido;

            // Asegurar que el directorio de croquis existe
            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists('croquis')) {
                \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('croquis');
            }

            // Procesar imágenes de los pisos si se envían (Multi-piso)
            if ($request->has('croquis_images')) {
                $images = $request->croquis_images;
                $floorPaths = [];
                foreach ($images as $piso => $imageData) {
                    $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
                    $imageData = str_replace(' ', '+', $imageData);
                    $imageName = 'croquis_acta_' . $id . '_piso_' . $piso . '.png';
                    $path = 'croquis/' . $imageName;
                    
                    $success = \Illuminate\Support\Facades\Storage::disk('public')->put($path, base64_decode($imageData));
                    if ($success) {
                        $floorPaths[$piso] = $path;
                        \Illuminate\Support\Facades\Log::info("Croquis PISO $piso guardado en: $path");
                    } else {
                        \Illuminate\Support\Facades\Log::error("Fallo al guardar croquis PISO $piso en: $path");
                    }
                }
                $contenido['piso_images'] = $floorPaths;
            }

            // Procesar la imagen del croquis principal (para compatibilidad)
            if ($request->has('croquis_image')) {
                $imageData = $request->croquis_image;
                $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
                $imageData = str_replace(' ', '+', $imageData);
                $imageName = 'croquis_acta_' . $id . '.png';
                $path = 'croquis/' . $imageName;
                
                $success = \Illuminate\Support\Facades\Storage::disk('public')->put($path, base64_decode($imageData));
                if ($success) {
                    $contenido['imagen_path'] = $path;
                    \Illuminate\Support\Facades\Log::info("Croquis PRINCIPAL guardado en: $path");
                }
            }
            
            // Buscamos si ya existe el registro
            $modulo = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
                                      ->where('modulo_nombre', $moduloNombre)
                                      ->first();

            if ($modulo) {
                // Si existe, actualizamos
                $modulo->update([
                    'contenido' => $contenido
                ]);
            } else {
                // Si no existe, insertamos manualmente
                $nextId = (MonitoreoModulos::max('id') ?? 0) + 1;
                
                MonitoreoModulos::create([
                    'id' => $nextId,
                    'cabecera_monitoreo_id' => $id,
                    'modulo_nombre' => $moduloNombre,
                    'contenido' => $contenido
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Croquis 2D guardado correctamente.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }
}
