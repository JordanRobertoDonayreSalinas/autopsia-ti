<?php

namespace App\Http\Controllers;

use App\Models\EquipoComputo;
use App\Models\CabeceraMonitoreo;
use App\Models\Establecimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\EquiposExport;
use App\Exports\Ficha42Export;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ReporteEquiposController extends Controller
{
    /**
     * Muestra la vista principal de reportes de equipos de cÃ³mputo.
     */
    public function index(Request $request)
    {
        // Manejar fechas con persistencia en sesiÃ³n
        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            // Si el usuario envÃ­a fechas, guardarlas en sesiÃ³n
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');

            session(['equipos_fecha_inicio' => $fechaInicio]);
            session(['equipos_fecha_fin' => $fechaFin]);
        } else {
            // Si no hay fechas en el request, usar las de sesiÃ³n o valores por defecto
            $fechaInicio = session('equipos_fecha_inicio', now()->startOfYear()->format('Y-m-d'));
            $fechaFin = session('equipos_fecha_fin', now()->format('Y-m-d'));
        }

        // Obtener listas para filtros
        $provincias = Establecimiento::whereIn('id', function ($subQuery) {
            $subQuery->select('establecimiento_id')
                ->from('mon_cabecera_monitoreo');
        })->distinct()->pluck('provincia')->filter()->sort();

        $establecimientos = Establecimiento::whereIn('id', function ($subQuery) {
            $subQuery->select('establecimiento_id')
                ->from('mon_cabecera_monitoreo');
        })->orderBy('nombre', 'asc')->get(['id', 'nombre']);

        // Obtener lista de mÃ³dulos ordenados con nombres amigables
        $modulos = \App\Helpers\ModuloHelper::getTodosLosModulos();

        // Inicializar query
        $query = EquipoComputo::with(['cabecera.establecimiento', 'cabecera.detalles']);

        // Aplicar filtro de fecha (siempre aplicado con valores por defecto)
        $query->whereHas('cabecera', function ($q) use ($fechaInicio) {
            $q->whereDate('fecha', '>=', $fechaInicio);
        });

        $query->whereHas('cabecera', function ($q) use ($fechaFin) {
            $q->whereDate('fecha', '<=', $fechaFin);
        });

        // Filtro por establecimiento
        if ($request->filled('establecimiento_id')) {
            $query->whereHas('cabecera', function ($q) use ($request) {
                $q->where('establecimiento_id', $request->establecimiento_id);
            });
        }

        // Filtro por provincia
        if ($request->filled('provincia')) {
            $query->whereHas('cabecera.establecimiento', function ($q) use ($request) {
                $q->where('provincia', $request->provincia);
            });
        }

        // Filtro por distrito
        if ($request->filled('distrito')) {
            $query->whereHas('cabecera.establecimiento', function ($q) use ($request) {
                $q->where('distrito', $request->distrito);
            });
        }

        // Filtro por módulo
        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        // Filtro por descripción
        if ($request->filled('descripcion')) {
            $query->where('descripcion', $request->descripcion);
        }

        // Filtro por tipo de establecimiento (ESPECIALIZADO/NO ESPECIALIZADO)
        if ($request->filled('tipo')) {
            $query->whereHas('cabecera.establecimiento', function ($q) use ($request) {
                $this->applyTipoFilter($q, $request->tipo);
            });
        }

        // Filtro: Última visita por módulo (fusión inteligente)
        if ($request->filled('solo_ultima_visita')) {
            $this->filtrarUltimaVisitaPorModulo($query, $request, $fechaInicio, $fechaFin);
        }
        
        // Obtener distritos y establecimientos filtrados para persistencia en los selectores
        $distritos = collect();
        if ($request->filled('provincia')) {
            $distritos = Establecimiento::where('provincia', $request->provincia)
                ->whereIn('id', function($q) {
                    $q->select('establecimiento_id')->from('mon_cabecera_monitoreo');
                })
                ->distinct()
                ->pluck('distrito')
                ->filter()
                ->sort();
        }

        $establecimientosQuery = Establecimiento::whereIn('id', function ($subQuery) {
            $subQuery->select('establecimiento_id')->from('mon_cabecera_monitoreo');
        });

        if ($request->filled('provincia')) {
            $establecimientosQuery->where('provincia', $request->provincia);
        }
        if ($request->filled('distrito')) {
            $establecimientosQuery->where('distrito', $request->distrito);
        }
        if ($request->filled('tipo')) {
            $this->applyTipoFilter($establecimientosQuery, $request->tipo);
        }

        $establecimientos = $establecimientosQuery->orderBy('nombre', 'asc')->get(['id', 'nombre']);

        // Ordenar por fecha más reciente
        $equipos = $query->orderBy('created_at', 'desc')->paginate(20);
        $equipos->setCollection($this->expandirPorConsultoriosFuncionales($equipos->getCollection()));

        // Obtener descripciones únicas
        $descripciones = EquipoComputo::distinct()->pluck('descripcion')->filter()->sort()->values();

        return view('usuario.reportes.equipos', compact('equipos', 'establecimientos', 'distritos', 'provincias', 'modulos', 'descripciones', 'fechaInicio', 'fechaFin'));
    }

    /**
     * Expande cada equipo en una o más "filas de reporte": una por el
     * consultorio donde realmente vive el registro, y una adicional por
     * cada consultorio FUNCIONAL vinculado que comparte ese mismo equipo
     * (ver ModuloHelper::getFuncionalesQueComparten). Así, un físico atado a
     * 2+ funcionales aparece representado en el reporte por cada uno de
     * ellos, no solo una vez bajo el nombre del físico.
     *
     * @return \Illuminate\Support\Collection Colección de objetos
     *         {equipo: EquipoComputo, modulo_efectivo: string}
     */
    private function expandirPorConsultoriosFuncionales($equipos)
    {
        $expandido = collect();

        foreach ($equipos as $equipo) {
            $expandido->push((object) ['equipo' => $equipo, 'modulo_efectivo' => $equipo->modulo]);

            if (!\App\Helpers\ModuloHelper::esModuloFijo($equipo->modulo)) {
                $funcionales = \App\Helpers\ModuloHelper::getFuncionalesQueComparten($equipo->cabecera, $equipo->modulo);
                foreach ($funcionales as $slugFuncional) {
                    $expandido->push((object) ['equipo' => $equipo, 'modulo_efectivo' => $slugFuncional]);
                }
            }
        }

        return $expandido;
    }

    /**
     * Exporta el reporte de equipos de cómputo a Excel.
     */
    public function exportarExcel(Request $request)
    {
        // Validar filtros
        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'establecimiento_id' => 'nullable|exists:establecimientos,id',
            'provincia' => 'nullable|string',
            'modulo' => 'nullable|string',
        ]);

        // Construir query con los mismos filtros
        $query = EquipoComputo::with(['cabecera.establecimiento', 'cabecera.detalles']);

        if ($request->filled('fecha_inicio')) {
            $query->whereHas('cabecera', function ($q) use ($request) {
                $q->whereDate('fecha', '>=', $request->fecha_inicio);
            });
        }

        if ($request->filled('fecha_fin')) {
            $query->whereHas('cabecera', function ($q) use ($request) {
                $q->whereDate('fecha', '<=', $request->fecha_fin);
            });
        }

        if ($request->filled('establecimiento_id')) {
            $query->whereHas('cabecera', function ($q) use ($request) {
                $q->where('establecimiento_id', $request->establecimiento_id);
            });
        }

        if ($request->filled('provincia')) {
            $query->whereHas('cabecera.establecimiento', function ($q) use ($request) {
                $q->where('provincia', $request->provincia);
            });
        }

        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        // Filtro por tipo de establecimiento
        if ($request->filled('tipo')) {
            $query->whereHas('cabecera.establecimiento', function ($q) use ($request) {
                $this->applyTipoFilter($q, $request->tipo);
            });
        }

        // Filtro: Última visita por módulo (fusión inteligente)
        if ($request->filled('solo_ultima_visita')) {
            $this->filtrarUltimaVisitaPorModulo(
                $query, $request,
                $request->input('fecha_inicio'),
                $request->input('fecha_fin')
            );
        }

        $equipos = $query->orderBy('created_at', 'desc')->get();
        $equiposExpandido = $this->expandirPorConsultoriosFuncionales($equipos);

        $filename = 'Reporte_Equipos_Computo_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new EquiposExport($equiposExpandido), $filename);
    }

    /**
     * Exporta la Ficha 42 (Anexo 1) a Excel.
     */
    public function exportarFicha42(Request $request)
    {
        $cabeceras = $this->ficha42GetCabeceras($request);

        $tiposReporte = [
            '01' => '01. PC (CPU + MONITOR) (CANTIDAD)',
            '02' => '02. IMPRESORA (CANTIDAD)',
            '03' => '03. IMPRESORA TIKETERA TERMICA (CANTIDAD)',
            '04' => '04. LECTORA DE DNI (CANTIDAD)',
            '05' => '05. LECTOR DE HUELLAS DACTILARES (CANTIDAD)',
            '06' => '06. SWITCH (CANTIDAD)',
            '07' => '07. RJ45 (CANTIDAD)',
            '08' => '08. CABLEADO (SI / NO)',
            '09' => '09. OPERADOR (CLARO, MOVISTAR, BITEL, E...)',
            '10' => '10. ANCHO DE BANDA EN (MB)',
            '11' => '11. FIBRA (SI / NO)',
            '12' => '12. COBRE (SI / NO)',
        ];

        $rows = [];
        foreach ($cabeceras as $cabecera) {
            $est = $cabecera->establecimiento;
            if (!$est) continue;

            $detallesDb  = DB::table('mon_detalle_modulos')->where('cabecera_monitoreo_id', $cabecera->id)->get()->keyBy('modulo_nombre');
            $monitoreoDb = DB::table('mon_monitoreo_modulos')->where('cabecera_monitoreo_id', $cabecera->id)->get()->keyBy('modulo_nombre');

            $listaEquipos = $this->ficha42RecolectarEquipos($cabecera, $detallesDb, $monitoreoDb);
            $infra        = $this->ficha42ContarInfraestructura($detallesDb, $monitoreoDb);
            $allDetalles  = $detallesDb->merge($monitoreoDb)->values();

            foreach ($tiposReporte as $key => $label) {
                $rows[] = $this->ficha42GenerarFila($key, $label, $est, $listaEquipos, $infra, $allDetalles);
            }
        }

        $filename = 'Ficha_42_Anexo1_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new Ficha42Export(collect($rows)), $filename);
    }

    /** Obtiene las cabeceras mÃ¡s recientes segÃºn los filtros del request */
    private function ficha42GetCabeceras(Request $request)
    {
        $subQuery = DB::table('mon_cabecera_monitoreo as c')
            ->join('establecimientos as e', 'c.establecimiento_id', '=', 'e.id')
            ->select('c.establecimiento_id', DB::raw('MAX(c.id) as max_id'))
            ->where('c.anulado', 0)
            ->when($request->filled('fecha_fin'), fn($q) => $q->whereDate('c.created_at', '<=', $request->fecha_fin))
            ->when($request->filled('establecimiento_id'), fn($q) => $q->where('c.establecimiento_id', $request->establecimiento_id))
            ->when($request->filled('provincia'), fn($q) => $q->where('e.provincia', $request->provincia))
            ->when($request->filled('distrito'), fn($q) => $q->where('e.distrito', $request->distrito))
            ->when($request->filled('tipo'), function ($q) use ($request) {
                $this->applyTipoFilter($q, $request->tipo, 'e.');
            })
            ->groupBy('c.establecimiento_id');

        $ids = $subQuery->pluck('max_id');
        return CabeceraMonitoreo::with(['establecimiento', 'equipos'])->whereIn('id', $ids)->get();
    }

    /** Recolecta equipos por módulo: prioriza SQL, hace fallback a JSON */
    private function ficha42RecolectarEquipos($cabecera, $detallesDb, $monitoreoDb)
    {
        // Normalizamos las llaves de las colecciones a minúsculas
        $equiposSqlGrouped = $cabecera->equipos->groupBy(fn($e) => strtolower(trim($e->modulo)));
        $detallesDb        = $detallesDb->keyBy(fn($v, $k) => strtolower(trim($k)));
        $monitoreoDb       = $monitoreoDb->keyBy(fn($v, $k) => strtolower(trim($k)));

        $modulosUnicos = $detallesDb->keys()
            ->merge($monitoreoDb->keys())
            ->merge($equiposSqlGrouped->keys())
            ->unique();
            
        $lista = collect();

        foreach ($modulosUnicos as $modKey) {
            $sqlItems = $equiposSqlGrouped->get($modKey);
            if ($sqlItems && $sqlItems->isNotEmpty()) {
                foreach ($sqlItems as $e) { $lista->push($e); }
                continue;
            }
            
            $det = $detallesDb->get($modKey) ?? $monitoreoDb->get($modKey);
            if (!$det) continue;
            
            $cont = is_string($det->contenido) ? json_decode($det->contenido, true) : (array)$det->contenido;
            if (!is_array($cont)) continue;
            
            $equiposJson = $cont['equipos_data'] ?? ($cont['inventario'] ?? ($cont['equipos'] ?? ($cont['equipos_de_computo'] ?? ($cont['data_equipos'] ?? ($cont['equipos_listado'] ?? [])))));
            if (!is_array($equiposJson)) continue;
            
            foreach ($equiposJson as $ej) {
                $obj = new \stdClass();
                $obj->modulo      = $modKey;
                $obj->descripcion = $ej['descripcion'] ?? ($ej['nombre'] ?? ($ej['equipo'] ?? 'PC'));
                $obj->cantidad    = $ej['cantidad'] ?? 1;
                $obj->propio      = $ej['propio'] ?? true;
                $lista->push($obj);
            }
        }
        return $lista;
    }

    /** Cuenta consultorios físicos y detecta conectividad por categoría */
    private function ficha42ContarInfraestructura($detallesDb, $monitoreoDb): array
    {
        $officesCount  = ['triaje' => 0, 'consultorio' => 0, 'admision' => 0, 'programacion' => 0];
        $cableadoCount = ['triaje' => 0, 'consultorio' => 0, 'admision' => 0, 'programacion' => 0];
        $fibraCount    = ['triaje' => 0, 'consultorio' => 0, 'admision' => 0, 'programacion' => 0];
        $cobreCount    = ['triaje' => 0, 'consultorio' => 0, 'admision' => 0, 'programacion' => 0];
        $hasRed = 0;
        $hasInternet = 0;

        foreach ($detallesDb->merge($monitoreoDb)->values() as $det) {
            $cont = is_string($det->contenido) ? json_decode($det->contenido, true) : (array)$det->contenido;
            if (!is_array($cont)) continue;

            $tipoConect = strtoupper($cont['tipo_conectividad'] ?? ($cont['tipo'] ?? ''));
            $operador   = strtoupper($cont['operador_servicio'] ?? ($cont['operador'] ?? ''));

            if ((!empty($cont['wifi_fuente']) && $cont['wifi_fuente'] !== 'NO') ||
                (!empty($operador) && !in_array($operador, ['NO', 'NINGUNO', 'N/A'])) ||
                (str_contains($tipoConect, 'CABLEADO') || str_contains($tipoConect, 'WIFI'))) {
                $hasRed = 1;
                $hasInternet = 1;
            }

            $numOffices = $cont['num_consultorios'] ?? ($cont['nro_consultorios'] ?? ($cont['numero_consultorio'] ?? ($cont['nro_ventanillas'] ?? ($cont['consultorio']['cantidad'] ?? ($cont['detalle_del_consultorio']['num_consultorios'] ?? ($cont['inicio_labores']['consultorios'] ?? 0))))));
            if (!is_numeric($numOffices)) $numOffices = 0;

            $cat = $this->getCategory($det->modulo_nombre);
            if ($cat) {
                $officesCount[$cat]  += $numOffices;
                if (str_contains($tipoConect, 'CABLEADO')) $cableadoCount[$cat] += $numOffices;
                
                // Detección Inteligente de Fibra
                $isFibra = str_contains($tipoConect, 'FIBRA') || 
                           str_contains($operador, 'WIN') || 
                           str_contains($operador, 'WOW') || 
                           str_contains($operador, 'NUBYX') ||
                           str_contains($operador, 'FIBRA');
                
                if ($isFibra) {
                    $fibraCount[$cat] += $numOffices;
                } else {
                    // Detección de Cobre (Si hay internet pero no es fibra)
                    $isCobre = str_contains($tipoConect, 'COBRE') || 
                               str_contains($tipoConect, 'ADSL') || 
                               str_contains($tipoConect, 'HFC') ||
                               (!empty($operador) && !in_array($operador, ['NO', 'NINGUNO', 'N/A']));
                    
                    if ($isCobre) {
                        $cobreCount[$cat] += $numOffices;
                    }
                }
            }
        }

        return compact('officesCount', 'cableadoCount', 'fibraCount', 'cobreCount', 'hasRed', 'hasInternet');
    }

    /** Genera una fila del reporte para un tipo de equipo dado */
    private function ficha42GenerarFila(string $key, string $label, $est, $listaEquipos, array $infra, $allDetalles): array
    {
        ['officesCount' => $officesCount, 'cableadoCount' => $cableadoCount,
         'fibraCount'   => $fibraCount,   'cobreCount'    => $cobreCount,
         'hasRed'        => $hasRed,      'hasInternet'   => $hasInternet] = $infra;

        $row = [
            'categoria'            => ($key === '01') ? $est->categoria : '',
            'codigo'               => ($key === '01') ? $est->codigo    : '',
            'nombre'               => ($key === '01') ? $est->nombre    : '',
            'tipo_equipo'          => $label,
            'triaje'               => 0, 'consultorio' => 0, 'admision' => 0, 'programacion' => 0,
            'red_val'              => '', 'internet_val' => '',
            'faltante_triaje'      => 0, 'faltante_consultorio' => 0,
            'faltante_admision'    => 0, 'faltante_programacion' => 0,
            'faltante_red_val'     => '', 'faltante_internet_val' => '',
        ];

        // --- Conteo de equipos (01-07) ---
        if (in_array($key, ['01', '02', '03', '04', '05', '06', '07'])) {
            $excluidos = ['PERSONAL', 'TRABAJADOR', 'N/A', 'NINGUNO', '0', ''];
            
            // Variables para sumar switch y rj45 a nivel global del establecimiento
            $totalSwitch = 0;
            $totalRj45 = 0;

            foreach ($listaEquipos as $equipo) {
                if (isset($equipo->propio)) {
                    $pv = strtoupper(trim((string)$equipo->propio));
                    if (in_array($pv, $excluidos) || $equipo->propio === false) continue;
                }
                $desc  = strtoupper(trim($equipo->descripcion));
                $match = match($key) {
                    '01' => str_contains($desc, 'CPU') || str_contains($desc, 'LAPTOP') || str_contains($desc, 'TABLET') || str_contains($desc, 'ALL IN ONE') || str_contains($desc, 'ALL-IN-ONE') || str_contains($desc, 'PC') || str_contains($desc, 'COMPUTADORA') || str_contains($desc, 'ESTACION'),
                    '02' => str_contains($desc, 'IMPRESORA') && !str_contains($desc, 'TIKETERA') && !str_contains($desc, 'TICKETERA'),
                    '03' => str_contains($desc, 'TIKETERA') || str_contains($desc, 'TICKETERA') || str_contains($desc, 'TERMICA'),
                    '04' => (str_contains($desc, 'LECTOR') || str_contains($desc, 'LECTORA')) && (str_contains($desc, 'DNI') || str_contains($desc, 'DNIE')),
                    '05' => str_contains($desc, 'HUELLA') || str_contains($desc, 'HUELLERO') || str_contains($desc, 'BIOMETRICO'),
                    '06' => str_contains($desc, 'SWITCH') || str_contains($desc, 'SWICHT') || str_contains($desc, 'SWICH'),
                    '07' => str_contains($desc, 'RJ45') || str_contains($desc, 'RJ-45') || str_contains($desc, 'PUNTO DE RED') || str_contains($desc, 'CABLE DE RED') || str_contains($desc, 'PATCH CORD'),
                    default => false,
                };
                if ($match) {
                    if ($key === '06') {
                        $totalSwitch += $equipo->cantidad;
                    } elseif ($key === '07') {
                        $totalRj45 += $equipo->cantidad;
                    } else {
                        $cat = $this->getCategory($equipo->modulo);
                        if ($cat) {
                            // REGLA BASE: Solo llenar si la columna está permitida para este tipo (según BASE.xlsx)
                            if ($key === '01') $row[$cat] += $equipo->cantidad;
                            if ($key === '02' && $cat !== 'triaje') $row[$cat] += $equipo->cantidad;
                            if ($key === '03' && $cat === 'admision') $row[$cat] += $equipo->cantidad;
                            if ($key === '04' && $cat === 'consultorio') $row[$cat] += $equipo->cantidad;
                            if ($key === '05' && $cat === 'admision') $row[$cat] += $equipo->cantidad;
                        }
                    }
                }
            }

            // Faltantes y Asignaciones Globales (01-07)
            if ($key === '01') {
                $row['faltante_triaje']      = max(0, $officesCount['triaje']      - $row['triaje']);
                $row['faltante_consultorio'] = max(0, $officesCount['consultorio'] - $row['consultorio']);
                $row['faltante_admision']    = max(0, $officesCount['admision']    - $row['admision']);
                $row['faltante_programacion'] = max(0, max(1, $officesCount['programacion']) - $row['programacion']);
                
                $row['red_val']              = $hasRed ? 'SI' : 'NO';
                $row['internet_val']         = $hasInternet ? 'SI' : 'NO';
                $row['faltante_red_val']     = ($hasRed == 0) ? 'SI' : '';
                $row['faltante_internet_val'] = ($hasInternet == 0) ? 'SI' : '';
            } elseif ($key === '02') {
                $row['faltante_consultorio'] = max(0, $officesCount['consultorio'] - $row['consultorio']);
                $row['faltante_admision']    = max(0, $officesCount['admision']    - $row['admision']);
                $row['faltante_programacion'] = max(0, $officesCount['programacion'] - $row['programacion']);
            } elseif ($key === '03') {
                $row['faltante_admision']    = max(0, $officesCount['admision'] - $row['admision']);
            } elseif ($key === '04') {
                $row['faltante_consultorio'] = max(0, $officesCount['consultorio'] - $row['consultorio']);
            } elseif ($key === '05') {
                $row['faltante_admision']    = max(0, $officesCount['admision'] - $row['admision']);
            } elseif ($key === '06') {
                $row['red_val'] = $totalSwitch > 0 ? $totalSwitch : '';
                $row['faltante_red_val'] = ''; 
            } elseif ($key === '07') {
                $row['red_val'] = $totalRj45 > 0 ? $totalRj45 : '';
                $row['faltante_red_val'] = ''; 
            }
        }

        // --- Conectividad (08-12) ---
        if (in_array($key, ['08', '09', '10', '11', '12'])) {
            switch ($key) {
                case '08': // CABLEADO -> Columna RED
                    $row['red_val'] = $hasRed ? 'SI' : 'NO';
                    $row['faltante_red_val'] = ($hasRed == 0) ? 'SI' : '';
                    break;
                case '09': // OPERADOR -> Columna INTERNET
                    foreach ($allDetalles as $det) {
                        $c = is_string($det->contenido) ? json_decode($det->contenido, true) : (array)$det->contenido;
                        $op = strtoupper($c['operador_servicio'] ?? ($c['operador'] ?? ''));
                        if ($op && !in_array($op, ['NO', 'NINGUNO', 'N/A'])) { $row['internet_val'] = $op; break; }
                    }
                    if (empty($row['internet_val'])) $row['internet_val'] = 'NO';
                    $row['faltante_internet_val'] = ($hasInternet == 0) ? 'SI' : '';
                    break;
                case '10': // ANCHO BANDA -> Columna INTERNET
                    foreach ($allDetalles as $det) {
                        $c = is_string($det->contenido) ? json_decode($det->contenido, true) : (array)$det->contenido;
                        $bandaCantidad = $c['velocidad_internet_cantidad'] ?? '';
                        $bandaUnidad = $c['velocidad_internet_unidad'] ?? '';
                        $descarga = $c['velocidad_descarga'] ?? '';
                        $subida = $c['velocidad_subida'] ?? '';
                        $descargaUnidad = $c['velocidad_descarga_unidad'] ?? $bandaUnidad;
                        $subidaUnidad = $c['velocidad_subida_unidad'] ?? $bandaUnidad;
                        
                        $valDescarga = $descarga ?: $bandaCantidad;
                        if ($valDescarga || $subida) {
                            $partes = [];
                            if ($valDescarga) $partes[] = "Descarga: {$valDescarga} {$descargaUnidad}";
                            if ($subida) $partes[] = "Subida: {$subida} {$subidaUnidad}";
                            $row['internet_val'] = implode(" / ", $partes);
                            break;
                        }

                        // Fallback compatibilidad
                        $band = $c['ancho_banda'] ?? ($c['velocidad'] ?? ($c['velocidad_internet'] ?? ''));
                        if ($band && (is_numeric($band) || str_contains(strtoupper($band), 'MBPS'))) { 
                            $row['internet_val'] = $band; 
                            break; 
                        }
                    }
                    $row['faltante_internet_val'] = ($hasInternet == 0) ? 'SI' : '';
                    break;
                case '11': // FIBRA -> Columna INTERNET
                    $totalFibra = array_sum($fibraCount);
                    $row['internet_val'] = ($totalFibra > 0) ? 'SI' : 'NO';
                    $row['faltante_internet_val'] = ($hasInternet == 0) ? 'SI' : '';
                    break;
                case '12': // COBRE -> Columna INTERNET
                    $totalCobre = array_sum($cobreCount);
                    $row['internet_val'] = ($totalCobre > 0) ? 'SI' : 'NO';
                    $row['faltante_internet_val'] = ($hasInternet == 0) ? 'SI' : '';
                    break;
            }
        }

        return $row;
    }

    /**
     * Mapea el nombre de un mÃ³dulo a su categorÃ­a de reporte
     */
    private function getCategory($modName)
    {
        $modName = strtolower(trim(str_replace('_', ' ', $modName)));
        
        if ($modName === 'triaje' || $modName === 'triaje esp' || str_contains($modName, 'triaje')) {
            return 'triaje';
        } elseif ($modName === 'citas' || $modName === 'citas esp' || $modName === 'admision' || str_contains($modName, 'caja')) {
            return 'admision';
        } elseif (str_contains($modName, 'gestion administrativa') || $modName === 'gestion admin esp' || str_contains($modName, 'estadistica') || str_contains($modName, 'direccion')) {
            return 'programacion';
        } elseif (
            str_contains($modName, 'medicina') || str_contains($modName, 'odontologia') || 
            str_contains($modName, 'nutricion') || str_contains($modName, 'psicologia') || 
            str_contains($modName, 'cred') || str_contains($modName, 'inmunizacion') || 
            str_contains($modName, 'prenatal') || str_contains($modName, 'planificacion') ||
            str_contains($modName, 'farmacia') || str_contains($modName, 'laboratorio') ||
            str_contains($modName, 'asistencia social') || str_contains($modName, 'topico') ||
            str_contains($modName, 'emergencia') || str_contains($modName, 'obstetricia')
        ) {
            return 'consultorio';
        }
        
        return null;
    }

    /**
     * Obtiene establecimientos filtrados por provincia y tipo
     */
    public function getEstablecimientos(Request $request)
    {
        $query = Establecimiento::whereIn('id', function ($subQuery) {
            $subQuery->select('establecimiento_id')
                ->from('mon_cabecera_monitoreo');
        });

        // Filtrar por provincia si se proporciona
        if ($request->filled('provincia')) {
            $query->where('provincia', $request->provincia);
        }

        // Filtrar por distrito si se proporciona
        if ($request->filled('distrito')) {
            $query->where('distrito', $request->distrito);
        }

        // Filtrar por tipo si se proporciona
        if ($request->filled('tipo')) {
            $this->applyTipoFilter($query, $request->tipo);
        }

        $establecimientos = $query->orderBy('nombre', 'asc')->get(['id', 'nombre']);
        return response()->json($establecimientos);
    }

    /**
     * Obtiene distritos filtrados por provincia y tipo
     */
    public function ajaxGetDistritos(Request $request)
    {
        $query = Establecimiento::whereIn('id', function ($subQuery) {
            $subQuery->select('establecimiento_id')
                ->from('mon_cabecera_monitoreo');
        });

        if ($request->filled('provincia')) {
            $query->where('provincia', $request->provincia);
        }

        // Aplicar filtro de tipo si existe
        if ($request->filled('tipo')) {
            $this->applyTipoFilter($query, $request->tipo);
        }

        $distritos = $query->distinct()->pluck('distrito')->filter()->sort()->values();
        return response()->json($distritos);
    }

    /**
     * Obtiene provincias filtradas por tipo
     */
    public function getProvincias(Request $request)
    {
        $query = Establecimiento::whereIn('id', function ($subQuery) {
            $subQuery->select('establecimiento_id')
                ->from('mon_cabecera_monitoreo');
        });

        // Filtrar por tipo si se proporciona
        if ($request->filled('tipo')) {
            $this->applyTipoFilter($query, $request->tipo);
        }

        $provincias = $query->distinct()->pluck('provincia')->filter()->sort()->values();
        return response()->json($provincias);
    }

    /**
     * Obtiene mÃ³dulos filtrados por establecimiento, provincia y tipo
     */
    public function getModulos(Request $request)
    {
        $query = EquipoComputo::query();

        // Filtrar por establecimiento
        if ($request->filled('establecimiento_id')) {
            $query->whereHas('cabecera', function ($q) use ($request) {
                $q->where('establecimiento_id', $request->establecimiento_id);
            });
        }

        // Filtrar por provincia
        if ($request->filled('provincia')) {
            $query->whereHas('cabecera.establecimiento', function ($q) use ($request) {
                $q->where('provincia', $request->provincia);
            });
        }

        // Filtrar por distrito
        if ($request->filled('distrito')) {
            $query->whereHas('cabecera.establecimiento', function ($q) use ($request) {
                $q->where('distrito', $request->distrito);
            });
        }

        // Filtrar por tipo
        if ($request->filled('tipo')) {
            $query->whereHas('cabecera.establecimiento', function ($q) use ($request) {
                $this->applyTipoFilter($q, $request->tipo);
            });
        }

        $modulosTecnicos = $query->distinct()->pluck('modulo')->filter()->sort()->values();

        $modulos = [];
        foreach ($modulosTecnicos as $moduloTecnico) {
            $modulos[] = [
                'valor' => $moduloTecnico,
                'nombre' => \App\Helpers\ModuloHelper::getNombreAmigable($moduloTecnico) ?? $moduloTecnico
            ];
        }

        return response()->json($modulos);
    }

    /**
     * Obtiene descripciones filtradas por establecimiento, provincia, tipo y mÃ³dulo
     */
    public function getDescripciones(Request $request)
    {
        $query = EquipoComputo::query();

        // Filtrar por establecimiento
        if ($request->filled('establecimiento_id')) {
            $query->whereHas('cabecera', function ($q) use ($request) {
                $q->where('establecimiento_id', $request->establecimiento_id);
            });
        }

        // Filtrar por provincia
        if ($request->filled('provincia')) {
            $query->whereHas('cabecera.establecimiento', function ($q) use ($request) {
                $q->where('provincia', $request->provincia);
            });
        }

        // Filtrar por distrito
        if ($request->filled('distrito')) {
            $query->whereHas('cabecera.establecimiento', function ($q) use ($request) {
                $q->where('distrito', $request->distrito);
            });
        }

        // Filtrar por tipo
        if ($request->filled('tipo')) {
            $query->whereHas('cabecera.establecimiento', function ($q) use ($request) {
                $this->applyTipoFilter($q, $request->tipo);
            });
        }

        // Filtrar por mÃ³dulo
        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        $descripciones = $query->distinct()->pluck('descripcion')->filter()->sort()->values();
        return response()->json($descripciones);
    }

    /**
     * Helper centralizado para el filtro de tipo de establecimiento
     */
    private function applyTipoFilter($query, $tipo, $prefix = '')
    {
        $codigosCSMC = ['25933', '28653', '27197', '34021', '25977', '33478', '27199', '30478'];
        $nombresCSMC = [
            'CSMC TUPAC AMARU',
            'CSMC COLOR ESPERANZA',
            'CSMC DECÍDETE A SER FELIZ',
            'CSMC SANTISIMA VIRGEN DE YAUCA',
            'CSMC VITALIZA',
            'CSMC CRISTO MORENO DE LUREN',
            'CSMC NUEVO HORIZONTE',
            'CSMC MENTE SANA'
        ];

        if ($tipo === 'ESPECIALIZADO') {
            $query->where(function ($q) use ($codigosCSMC, $nombresCSMC, $prefix) {
                $q->whereIn($prefix . 'codigo', $codigosCSMC)
                    ->orWhereIn(DB::raw('UPPER(TRIM(' . $prefix . 'nombre))'), $nombresCSMC);
            });
        } elseif ($tipo === 'NO ESPECIALIZADO') {
            $query->where(function ($q) use ($codigosCSMC, $nombresCSMC, $prefix) {
                $q->where(function ($sq) use ($codigosCSMC, $prefix) {
                    $sq->whereNotIn($prefix . 'codigo', $codigosCSMC)
                       ->orWhereNull($prefix . 'codigo');
                })->where(function ($sq) use ($nombresCSMC, $prefix) {
                    $sq->whereNotIn(DB::raw('UPPER(TRIM(' . $prefix . 'nombre))'), $nombresCSMC)
                       ->orWhereNull($prefix . 'nombre');
                });
            });
        }
    }

    /**
     * Fusión inteligente de visitas por tipo de equipo:
     *
     * Agrupa por (establecimiento, módulo, descripción de equipo) y toma
     * la cabecera más reciente que tiene registro de ese tipo de equipo.
     *
     * Esto resuelve el caso donde en la primera visita se registró CPU+Laptop
     * en Medicina, y en la segunda visita se registró el Lector DNI Electrónico
     * para el mismo módulo. Los tres equipos aparecen en el reporte final,
     * cada uno desde la visita donde fue registrado más recientemente.
     *
     * Ejemplo - Medicina en Establecimiento X:
     *   Visita 1: CPU, Laptop           → max_cabecera = 10
     *   Visita 2: Lector DNI electrónico → max_cabecera = 25
     * Resultado: CPU (cab.10) + Laptop (cab.10) + Lector DNI (cab.25)
     */
    private function filtrarUltimaVisitaPorModulo($query, Request $request, $fechaInicio = null, $fechaFin = null): void
    {
        // Paso 1: MAX(cabecera_id) agrupado por (establecimiento, módulo, descripción de equipo)
        // Cada tipo de equipo tiene su propia "última visita ganadora"
        $latestPorTipoEquipo = DB::table('mon_equipos_computo as eq')
            ->join('mon_cabecera_monitoreo as c', 'eq.cabecera_monitoreo_id', '=', 'c.id')
            ->join('establecimientos as e', 'c.establecimiento_id', '=', 'e.id')
            ->select(
                'c.establecimiento_id',
                'eq.modulo',
                'eq.descripcion',
                DB::raw('MAX(eq.cabecera_monitoreo_id) as max_cabecera_id')
            )
            ->where('c.anulado', 0)
            ->when($fechaInicio, fn($q) => $q->whereDate('c.fecha', '>=', $fechaInicio))
            ->when($fechaFin,    fn($q) => $q->whereDate('c.fecha', '<=', $fechaFin))
            ->when($request->filled('establecimiento_id'), fn($q) => $q->where('c.establecimiento_id', $request->establecimiento_id))
            ->when($request->filled('provincia'), fn($q) => $q->where('e.provincia', $request->provincia))
            ->when($request->filled('distrito'),  fn($q) => $q->where('e.distrito',  $request->distrito))
            ->when($request->filled('modulo'),    fn($q) => $q->where('eq.modulo',   $request->modulo))
            ->groupBy('c.establecimiento_id', 'eq.modulo', 'eq.descripcion')
            ->get();

        if ($latestPorTipoEquipo->isEmpty()) {
            $query->whereRaw('1 = 0');
            return;
        }

        // Paso 2: Solo pasan los registros cuyo (cabecera_id, modulo, descripcion)
        // coincide exactamente con el triplete ganador de su establecimiento
        $query->where(function ($q) use ($latestPorTipoEquipo) {
            foreach ($latestPorTipoEquipo as $row) {
                $q->orWhere(function ($q2) use ($row) {
                    $q2->where('cabecera_monitoreo_id', $row->max_cabecera_id)
                       ->where('modulo',      $row->modulo)
                       ->where('descripcion', $row->descripcion);
                });
            }
        });
    }
}
