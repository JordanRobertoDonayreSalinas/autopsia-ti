<?php

namespace App\Http\Controllers;

use App\Models\CabeceraMonitoreo;
use App\Models\MonitoreoModulos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
        $acta = CabeceraMonitoreo::findOrFail($id);

        return response()->json([
            'modulos' => $modulosData,
            // Datos del acta a nivel de establecimiento (no de un consultorio en
            // particular), para que "Sincronizar módulos" también los refresque
            // sin depender de recargar la página.
            'pozo_tierra'               => $acta->pozo_tierra ?? 'NO',
            'pozo_tierra_cantidad'      => (int) ($acta->pozo_tierra_cantidad ?? 0),
            'pozo_tierra_operativos'    => (int) ($acta->pozo_tierra_operativos ?? 0),
            'pozo_tierra_inoperativos'  => (int) ($acta->pozo_tierra_inoperativos ?? 0),
            'panel_solar'               => $acta->panel_solar ?? 'NO',
            'panel_solar_cantidad'      => (int) ($acta->panel_solar_cantidad ?? 0),
            'panel_solar_operativos'    => (int) ($acta->panel_solar_operativos ?? 0),
            'panel_solar_inoperativos'  => (int) ($acta->panel_solar_inoperativos ?? 0),
        ]);
    }

    /**
     * Calles reales más cercanas al establecimiento, según OpenStreetMap
     * (Overpass API), para que el editor pueda dibujarlas solo alrededor
     * del croquis con su nombre real en vez de que el usuario las escriba
     * a mano. Se cachean 30 días por coordenada: el callejero no cambia.
     */
    public function callesCercanas($id)
    {
        $acta = CabeceraMonitoreo::with('establecimiento')->findOrFail($id);
        $lat = $acta->establecimiento->latitud ?? null;
        $lng = $acta->establecimiento->longitud ?? null;

        if (is_null($lat) || is_null($lng)) {
            return response()->json(['calles' => []]);
        }

        $cacheKey = 'calles_cercanas_' . round((float) $lat, 5) . '_' . round((float) $lng, 5);

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return response()->json(['calles' => $cached]);
        }

        $calles = $this->_consultarCallesOverpass((float) $lat, (float) $lng);

        // Un fallo de red no se cachea, para que el siguiente intento pueda
        // reintentar — el servidor público de Overpass suele saturarse y
        // responder 429/406/504. Un resultado con calles sí es estable (el
        // callejero no cambia) y se cachea 30 días; pero un resultado VACÍO
        // se cachea solo unas horas, porque Overpass puede responder 200
        // con la lista vacía cuando está sobrecargado sin llegar a fallar
        // como para que se note — así, si fue un vacío falso, se autocorrige
        // pronto en vez de quedar "sin calles" fijo para ese establecimiento.
        if ($calles !== null) {
            Cache::put($cacheKey, $calles, $calles ? now()->addDays(30) : now()->addHours(2));
        }

        return response()->json(['calles' => $calles ?? []]);
    }

    /**
     * Consulta Overpass por las vías con nombre alrededor de un punto,
     * probando más de un espejo público (el principal se satura seguido).
     * Devuelve null si ningún espejo respondió, para distinguir "sin calles
     * cerca" (array vacío, sí cacheable) de "no se pudo consultar" (no
     * cacheable).
     */
    private function _consultarCallesOverpass(float $lat, float $lng): ?array
    {
        $espejos = [
            'https://overpass-api.de/api/interpreter',
            'https://overpass.kumi.systems/api/interpreter',
        ];

        $query = "[out:json][timeout:15];(way(around:200,{$lat},{$lng})[\"highway\"][\"name\"];);out tags center;";

        foreach ($espejos as $url) {
            try {
                // El "Accept" que Laravel manda por defecto hace que el
                // servidor de Overpass responda 406 (content negotiation);
                // con un Accept/User-Agent genéricos, tipo curl, acepta bien.
                $response = Http::timeout(12)
                    ->withHeaders(['Accept' => '*/*', 'User-Agent' => 'curl/8.0'])
                    ->asForm()
                    ->post($url, ['data' => $query]);
                if (!$response->successful()) continue;

                $elements = $response->json('elements') ?? [];
                $vistos = [];
                $resultado = [];

                foreach ($elements as $el) {
                    $nombre = trim($el['tags']['name'] ?? '');
                    if ($nombre === '' || isset($vistos[$nombre])) continue;

                    $clat = $el['center']['lat'] ?? null;
                    $clon = $el['center']['lon'] ?? null;
                    if ($clat === null || $clon === null) continue;

                    $vistos[$nombre] = true;
                    $resultado[] = [
                        'nombre'    => $nombre,
                        'tipo'      => $this->_tipoVia($nombre, $el['tags']['highway'] ?? ''),
                        'lat'       => $clat,
                        'lon'       => $clon,
                        'distancia' => $this->_distanciaMetros($lat, $lng, $clat, $clon),
                    ];
                }

                usort($resultado, fn($a, $b) => $a['distancia'] <=> $b['distancia']);

                // Ya no se limita a 4 (uno por lado del croquis): ahora se dibujan
                // como etiquetas sobre el mapa real, en su propia posición, así que
                // se pueden mostrar todas las que haya cerca sin que se estorben.
                return array_slice($resultado, 0, 8);
            } catch (\Throwable $e) {
                Log::warning('[Calles cercanas] Error consultando ' . $url . ': ' . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Clasifica una vía como avenida/jirón/pasaje: primero por el prefijo de
     * su nombre real (así aparece casi siempre en el mapa de Perú), y si no
     * trae prefijo, por el tipo de vía que registra OpenStreetMap.
     */
    private function _tipoVia(string $nombre, string $highway): string
    {
        $n = mb_strtoupper($nombre, 'UTF-8');
        if (str_starts_with($n, 'AV.') || str_starts_with($n, 'AVENIDA')) return 'avenida';
        if (str_starts_with($n, 'JR.') || str_starts_with($n, 'JIRON') || str_starts_with($n, 'JIRÓN')) return 'jiron';
        if (str_starts_with($n, 'PSJ.') || str_starts_with($n, 'PJE.') || str_starts_with($n, 'PASAJE')) return 'pasaje';

        return match ($highway) {
            'trunk', 'primary', 'secondary' => 'avenida',
            'service', 'pedestrian', 'footway', 'path', 'living_street' => 'pasaje',
            default => 'jiron',
        };
    }

    /** Distancia en metros entre dos coordenadas (fórmula de Haversine). */
    private function _distanciaMetros(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }

    /**
     * Colores estables para los cursores de colaboración: cada usuario siempre
     * ve el mismo color para otro usuario, sin necesidad de guardarlo en BD.
     */
    private const COLORES_COLAB = [
        '#ef4444', '#f97316', '#eab308', '#22c55e', '#06b6d4',
        '#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6', '#f43f5e',
    ];

    /**
     * Edición colaborativa del croquis en tiempo real (por sondeo cada 900ms
     * desde el editor). Cada usuario sube su posición de cursor y su copia de
     * elementos/conexiones/eliminados; el servidor le devuelve el mismo estado
     * de los demás usuarios activos en esta acta, para que el editor haga el
     * merge (por timestamp) y dibuje sus cursores, nombre y cantidad.
     */
    public function croquisSync(Request $request, $id)
    {
        $userId = auth()->id();
        $now = now();

        DB::table('mon_croquis_presencia')->updateOrInsert(
            ['cabecera_monitoreo_id' => $id, 'user_id' => $userId],
            [
                'cursor_x'     => (int) $request->input('cursor_x', 0),
                'cursor_y'     => (int) $request->input('cursor_y', 0),
                'elements'     => json_encode($request->input('elements', [])),
                'connections'  => json_encode($request->input('connections', [])),
                'deleted_ids'  => json_encode($request->input('deletedIds', [])),
                'last_seen_at' => $now,
            ]
        );

        // Un usuario sin señal en los últimos segundos se considera desconectado
        // (pestaña cerrada de golpe, sin red, etc.) y deja de contar como activo.
        $limite = $now->copy()->subSeconds(6);

        $otros = DB::table('mon_croquis_presencia')
            ->where('cabecera_monitoreo_id', $id)
            ->where('user_id', '!=', $userId)
            ->where('last_seen_at', '>=', $limite)
            ->get();

        $userIds = $otros->pluck('user_id');
        $usuarios = \App\Models\User::whereIn('id', $userIds)->get()->keyBy('id');

        $colaboradores = $otros->map(function ($fila) use ($usuarios) {
            $user = $usuarios->get($fila->user_id);
            return [
                'user_id'     => $fila->user_id,
                'user_name'   => $user ? $user->full_name : 'Usuario',
                'color'       => self::COLORES_COLAB[$fila->user_id % count(self::COLORES_COLAB)],
                'cursor_x'    => (int) $fila->cursor_x,
                'cursor_y'    => (int) $fila->cursor_y,
                'elements'    => json_decode($fila->elements, true) ?? [],
                'connections' => json_decode($fila->connections, true) ?? [],
                'deletedIds'  => json_decode($fila->deleted_ids, true) ?? [],
            ];
        })->values();

        return response()->json([
            'ok' => true,
            'colaboradores' => $colaboradores,
            // Hora del servidor (ms desde época): el editor la usa para corregir
            // el desfase del reloj de cada navegador al decidir, en la fusión de
            // cambios simultáneos, cuál es realmente el más reciente — comparar
            // los `_ts` de cada usuario tal cual, sin corregir, hace que quien
            // tenga el reloj adelantado "gane" aunque su cambio sea más viejo.
            'server_time' => (int) round(microtime(true) * 1000),
        ]);
    }

    /**
     * El usuario cierra o navega fuera del croquis: se retira de inmediato de
     * la lista de colaboradores (no hay que esperar a que expire por inactividad).
     * Se llama vía navigator.sendBeacon, que no permite cabeceras propias, así
     * que el token CSRF viaja en el cuerpo del formulario.
     */
    public function croquisLeave(Request $request, $id)
    {
        DB::table('mon_croquis_presencia')
            ->where('cabecera_monitoreo_id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        return response()->json(['ok' => true]);
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
            $orden = ['all_in_one', 'pc', 'laptop', 'tablet', 'monitor', 'impresora', 'ticketera', 'escaner', 'lector_dnie', 'ups', 'teclado', 'mouse', 'equipo'];
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

            /*
             * Sistema que usa actualmente el consultorio (pregunta propia de los
             * consultorios dinámicos). Se normaliza al mismo subtype que dibuja
             * el croquis para poder sincronizar el ícono automáticamente.
             */
            $sistemaActualLabel = mb_strtoupper(trim((string)($content['sistema_actual'] ?? '')), 'UTF-8');
            $mapaSistemas = [
                'TUA'           => 'tua',
                'SIHCE'         => 'sihce',
                'SISMED'        => 'sismed',
                'HISMINSA'      => 'hisminsa',
                'SIS GALENPLUS' => 'sisgalenplus',
            ];
            $sistemaActual = $mapaSistemas[$sistemaActualLabel] ?? '';

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

            /*
             * Tipo de consultorio (FÍSICO/FUNCIONAL): solo lo responden los consultorios
             * dinámicos, en su propio formulario. Los módulos fijos (citas, urgencias,
             * farmacia...) no traen esta pregunta, así que su ambiente se sigue
             * infiriendo del nombre del servicio, como hasta ahora.
             */
            $tipoConsultorio = mb_strtoupper(trim((string)($content['tipo_consultorio'] ?? '')), 'UTF-8');
            if ($tipoConsultorio === 'FÍSICO') $tipoConsultorio = 'FISICO';

            /*
             * Piso: solo los consultorios dinámicos lo preguntan ("¿Qué piso es?"
             * en su propia ficha). Los módulos fijos no traen esa pregunta, así
             * que se asume piso 1, igual que el resto del croquis por defecto.
             */
            $piso = (int) ($content['piso'] ?? 1);
            if ($piso < 1) $piso = 1;

            $result[] = [
                'slug'              => $slug,
                'label'             => $label,
                'activo'            => $activo,
                'cantidad'          => $cantidad,
                'equipos'           => $equipos,        // [{ tipo, estado, cantidad, descripcion }]
                'total_equipos'     => $totalEquipos,
                'utiliza_sihce'     => $utiliza_sihce,
                'sistema_actual'    => $sistemaActual,   // 'tua'|'sihce'|'sismed'|'hisminsa'|'sisgalenplus'|''
                'tipo_conectividad' => $tipo_conectividad,
                'tipo_consultorio'  => $tipoConsultorio, // 'FISICO' | 'FUNCIONAL' | '' (módulos fijos)
                'piso'              => $piso,            // piso declarado en la ficha del consultorio
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
            // "ALL IN ONE" y "CPU/COMPUTADORA/DESKTOP" son equipos físicamente distintos
            // (una sola pieza vs. torre + monitor aparte): antes se agrupaban en un solo
            // tipo 'pc' y el croquis terminaba mostrando, por ejemplo, "ALL-IN-ONE ×2"
            // cuando en realidad había 1 all-in-one + 1 CPU registrados por separado.
            'all_in_one' => ['ALL IN ONE', 'ALL-IN-ONE'],
            'pc'         => ['CPU', 'COMPUTADORA', 'DESKTOP', 'PC '],
            'laptop'     => ['LAPTOP', 'PORTATIL', 'NOTEBOOK'],
            'tablet'     => ['TABLET'],
            'monitor'    => ['MONITOR', 'PANTALLA'],
            'teclado'    => ['TECLADO'],
            'mouse'      => ['MOUSE', 'RATON'],
            'impresora'  => ['IMPRESORA', 'MULTIFUNCIONAL', 'PLOTTER'],
            'ticketera'  => ['TICKETERA', 'TICKET'],
            // Mismo caso: un lector de DNIe no es un escáner de documentos, así que
            // se separan en tipos distintos en vez de sumarse bajo un solo icono.
            'lector_dnie' => ['DNIE', 'DNI ELECTRONICO', 'LECTOR', 'LECTORA'],
            'escaner'    => ['SCANNER', 'SCANER', 'ESCANER'],
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
