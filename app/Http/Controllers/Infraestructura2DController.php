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

    /**
     * Equipos y datos de cada módulo del acta, listos para dibujarse en el croquis.
     *
     * Los equipos se agrupan por tipo y estado: así el croquis muestra un icono por
     * tipo con su contador (IMPRESORA ×2) y el color puede reflejar el estado real.
     * Un módulo sin equipos registrados devuelve la lista vacía y no se dibuja.
     */
    private function _buildModulosData(int $actaId): array
    {
        // Nombre legible por slug. Los que no estén aquí se derivan del propio slug.
        $labels = [
            'citas'                     => 'Citas',
            'citas_esp'                 => 'Citas ESP',
            'triaje'                    => 'Triaje',
            'triaje_esp'                => 'Triaje ESP',
            'atencion_prenatal'         => 'Atención Prenatal',
            'parto'                     => 'Parto',
            'puerperio'                 => 'Puerperio',
            'cred'                      => 'CRED',
            'consulta_medicina'         => 'Medicina',
            'consulta_odontologia'      => 'Odontología',
            'consulta_psicologia'       => 'Psicología',
            'consulta_nutricion'        => 'Nutrición',
            'farmacia'                  => 'Farmacia',
            'farmacia_esp'              => 'Farmacia ESP',
            'laboratorio'               => 'Laboratorio',
            'inmunizaciones'            => 'Inmunizaciones',
            'planificacion_familiar'    => 'Planificación Familiar',
            'referencias'               => 'Referencias',
            'urgencias'                 => 'Urgencias',
            'fua_electronico'           => 'FUA Electrónico',
            'gestion_administrativa'    => 'Gestión Administrativa',
            'gestion_admin_esp'         => 'Gestión Adm. ESP',
            'sm_medicina_general'       => 'SM Medicina General',
            'sm_med_familiar'           => 'SM Medicina Familiar',
            'sm_psicologia'             => 'SM Psicología',
            'sm_psiquiatria'            => 'SM Psiquiatría',
            'sm_enfermeria'             => 'SM Enfermería',
            'sm_terapias'               => 'SM Terapias',
            'sm_servicio_social'        => 'SM Servicio Social',
        ];

        // Módulos que no representan un servicio con equipos
        $excluidos = ['infraestructura_2d', 'infraestructura_3d', 'config_modulos'];

        /*
         * Equipos de la tabla de inventario, agrupados por módulo.
         * No se usa una lista blanca de módulos: se consulta por el slug del módulo,
         * de modo que cualquier servicio nuevo funciona sin tocar este código.
         */
        // El modelo devuelve los textos en mayúsculas (UppercaseAttributes), mientras que
        // los módulos se identifican en minúsculas: se normaliza la clave para que casen.
        $equiposTabla = \App\Models\EquipoComputo::where('cabecera_monitoreo_id', $actaId)
            ->get()
            ->groupBy(fn($eq) => mb_strtolower(trim((string)$eq->modulo), 'UTF-8'));

        $modulosNuevos = DB::table('mon_detalle_modulos')
            ->where('cabecera_monitoreo_id', $actaId)
            ->get()
            ->keyBy('modulo_nombre');

        $modulosAntiguos = DB::table('mon_monitoreo_modulos')
            ->where('cabecera_monitoreo_id', $actaId)
            ->get();

        /*
         * Módulos activos del establecimiento: el módulo 'config_modulos' guarda la
         * lista de servicios habilitados. Un servicio activo se dibuja en el croquis
         * aunque todavía no tenga equipos registrados.
         */
        $configRow = $modulosAntiguos->firstWhere('modulo_nombre', 'config_modulos');
        $activos = [];
        if ($configRow) {
            $cfg = is_string($configRow->contenido) ? json_decode($configRow->contenido, true) : $configRow->contenido;
            if (is_array($cfg)) {
                foreach ($cfg as $clave => $valor) {
                    if (is_string($valor)) {
                        $activos[] = $valor;                       // ['citas', 'triaje', …]
                    } elseif (is_array($valor)) {
                        $slug = $valor['slug'] ?? $valor['modulo_nombre'] ?? null;
                        if ($slug) $activos[] = $slug;             // [{slug: 'citas'}, …]
                    } elseif (is_bool($valor) && $valor && is_string($clave)) {
                        $activos[] = $clave;                       // ['citas' => true, …]
                    }
                }
            }
        }
        $activos = array_values(array_unique(array_map('strval', $activos)));
        $hayConfig = count($activos) > 0;

        $registros = collect();
        $nombres = $modulosNuevos->keys()
            ->merge($modulosAntiguos->pluck('modulo_nombre'))
            ->merge($equiposTabla->keys())   // un servicio con equipos cuenta aunque no tenga ficha
            ->merge($activos)                // y un servicio activo cuenta aunque no tenga nada más
            ->unique();

        foreach ($nombres as $nombre) {
            if (in_array($nombre, $excluidos)) continue;

            if ($modulosNuevos->has($nombre)) {
                $registros->push($modulosNuevos->get($nombre));
            } else {
                $old = $modulosAntiguos->firstWhere('modulo_nombre', $nombre);
                $registros->push($old ?: (object)['modulo_nombre' => $nombre, 'contenido' => null]);
            }
        }

        $result = [];

        foreach ($registros as $reg) {
            $slug    = $reg->modulo_nombre;
            $content = is_string($reg->contenido) ? json_decode($reg->contenido, true) : ($reg->contenido ?? []);
            if (!is_array($content)) $content = [];

            // ── 1. Equipos: primero el inventario; si no hay, el listado del propio módulo ──
            $crudos = [];

            foreach ($equiposTabla->get($slug, collect()) as $eq) {
                $desc = trim((string)($eq->descripcion ?? ''));
                if ($desc === '') continue;
                $crudos[] = [
                    'descripcion' => mb_strtoupper($desc, 'UTF-8'),
                    'cantidad'    => max(1, (int)($eq->cantidad ?? 1)),
                    'estado'      => mb_strtoupper(trim((string)($eq->estado ?? 'OPERATIVO')), 'UTF-8'),
                ];
            }

            if (empty($crudos)) {
                $listado = $content['equipos_listado'] ?? $content['equipos'] ?? [];
                if (is_array($listado)) {
                    foreach ($listado as $eq) {
                        if (is_array($eq)) {
                            $desc     = trim((string)($eq['nombre'] ?? $eq['descripcion'] ?? ''));
                            $cantidad = max(1, (int)($eq['cantidad'] ?? 1));
                            $estado   = mb_strtoupper(trim((string)($eq['estado'] ?? 'OPERATIVO')), 'UTF-8');
                        } else {
                            $desc = trim((string)$eq);
                            $cantidad = 1;
                            $estado = 'OPERATIVO';
                        }
                        if ($desc === '') continue;
                        $crudos[] = [
                            'descripcion' => mb_strtoupper($desc, 'UTF-8'),
                            'cantidad'    => $cantidad,
                            'estado'      => $estado,
                        ];
                    }
                }
            }

            // ── 2. Agrupar por tipo de equipo y estado ──
            $agrupados = [];
            foreach ($crudos as $eq) {
                $tipo   = $this->_tipoEquipo($eq['descripcion']);
                $estado = $this->_normalizarEstado($eq['estado']);
                $clave  = $tipo . '|' . $estado;

                if (!isset($agrupados[$clave])) {
                    $agrupados[$clave] = [
                        'tipo'        => $tipo,
                        'estado'      => $estado,
                        'cantidad'    => 0,
                        'descripcion' => $eq['descripcion'],
                    ];
                }
                $agrupados[$clave]['cantidad'] += $eq['cantidad'];
            }

            // Orden estable: primero los equipos principales, luego los accesorios
            $orden = ['pc', 'laptop', 'tablet', 'monitor', 'impresora', 'ticketera', 'escaner', 'ups', 'teclado', 'mouse', 'equipo'];
            $equipos = array_values($agrupados);
            usort($equipos, function ($a, $b) use ($orden) {
                $ia = array_search($a['tipo'], $orden); $ib = array_search($b['tipo'], $orden);
                if ($ia === false) $ia = 99;
                if ($ib === false) $ib = 99;
                return $ia === $ib ? strcmp($a['estado'], $b['estado']) : $ia - $ib;
            });

            $totalEquipos = array_sum(array_column($equipos, 'cantidad'));

            // ── 3. Conectividad y sistemas ──
            $utiliza_sihce     = mb_strtoupper(trim((string)($content['utiliza_sihce'] ?? '')), 'UTF-8');
            $tipo_conectividad = mb_strtoupper(trim((string)($content['tipo_conectividad'] ?? '')), 'UTF-8');

            // ── 4. Ambientes declarados por el módulo ──
            $cantidad = (int)($content['nro_consultorios']
                ?? $content['num_consultorios']
                ?? $content['n_consultorios']
                ?? $content['cantidad_consultorios']
                ?? 1);
            if ($cantidad < 1) $cantidad = 1;

            /*
             * Un módulo se considera activo si está en la configuración del
             * establecimiento. En actas antiguas, que no tienen esa configuración,
             * se toma como activo el que ya tiene ficha o equipos registrados.
             *
             * Los consultorios dinámicos (creados con "Nuevo consultorio", identificables
             * porque su contenido trae 'titulo_consultorio') quedan fuera de esta regla:
             * no existe ningún checkbox para activarlos o desactivarlos, así que la lista
             * congelada de config_modulos nunca los va a contener. Sin esta excepción,
             * en cuanto exista un config_modulos (aunque sea de otro módulo distinto),
             * todo consultorio dinámico —con todo y sus equipos— deja de dibujarse en el
             * croquis para siempre, sin ninguna forma de reactivarlo desde la interfaz.
             */
            $tieneFicha  = $modulosNuevos->has($slug) || $modulosAntiguos->contains('modulo_nombre', $slug);
            $esDinamico  = array_key_exists('titulo_consultorio', $content);
            $activo = $esDinamico
                ? true
                : ($hayConfig ? in_array($slug, $activos, true) : ($tieneFicha || $totalEquipos > 0));

            /*
             * Nombre a mostrar: el consultorio dinámico ya trae su propio título
             * ("MEDICINA GENERAL 01"), sin el sufijo de timestamp que lleva el slug
             * ("medicina_general_01_1786641034"). Ese título es el que debe verse.
             */
            $label = $esDinamico && !empty($content['titulo_consultorio'])
                ? mb_strtoupper(trim((string)$content['titulo_consultorio']), 'UTF-8')
                : ($labels[$slug] ?? ucwords(str_replace('_', ' ', $slug)));

            $result[] = [
                'slug'              => $slug,
                'label'             => $label,
                'activo'            => $activo,
                'cantidad'          => $cantidad,
                'equipos'           => $equipos,        // [{ tipo, estado, cantidad, descripcion }]
                'total_equipos'     => $totalEquipos,
                'utiliza_sihce'     => $utiliza_sihce,
                'tipo_conectividad' => $tipo_conectividad,
            ];
        }

        // Activos primero y, dentro de ellos, los que tienen más equipos
        usort($result, function ($a, $b) {
            if ($a['activo'] !== $b['activo']) return $a['activo'] ? -1 : 1;
            return $b['total_equipos'] <=> $a['total_equipos'] ?: strcmp($a['label'], $b['label']);
        });

        return $result;
    }

    /**
     * Traduce la descripción libre del inventario al tipo de equipo que dibuja el croquis.
     */
    private function _tipoEquipo(string $descripcion): string
    {
        $d = mb_strtoupper($descripcion, 'UTF-8');
        $d = strtr($d, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U']);

        $reglas = [
            'pc'         => ['ALL IN ONE', 'ALL-IN-ONE', 'CPU', 'COMPUTADORA', 'DESKTOP', 'PC '],
            'laptop'     => ['LAPTOP', 'PORTATIL', 'NOTEBOOK'],
            'tablet'     => ['TABLET'],
            'monitor'    => ['MONITOR', 'PANTALLA'],
            'teclado'    => ['TECLADO'],
            'mouse'      => ['MOUSE', 'RATON'],
            'impresora'  => ['IMPRESORA', 'MULTIFUNCIONAL', 'PLOTTER'],
            'ticketera'  => ['TICKETERA', 'TICKET'],
            'escaner'    => ['DNIE', 'DNI ELECTRONICO', 'LECTOR', 'LECTORA', 'SCANNER', 'SCANER', 'ESCANER'],
            'ups'        => ['UPS', 'ESTABILIZADOR', 'STABILIZADOR', 'BATERIA'],
        ];

        foreach ($reglas as $tipo => $claves) {
            foreach ($claves as $clave) {
                if (str_contains($d, $clave)) return $tipo;
            }
        }
        // Si aparece un equipo que no reconocemos, se dibuja igual con el icono genérico
        return 'equipo';
    }

    /**
     * Estados posibles del inventario: OPERATIVO · REGULAR · INOPERATIVO.
     */
    private function _normalizarEstado(string $estado): string
    {
        $e = mb_strtoupper(trim($estado), 'UTF-8');
        if (str_contains($e, 'INOPERATIV') || str_contains($e, 'BAJA') || str_contains($e, 'MALOGRAD')) return 'INOPERATIVO';
        if (str_contains($e, 'REGULAR')) return 'REGULAR';
        return 'OPERATIVO';
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
