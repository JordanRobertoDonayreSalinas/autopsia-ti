<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Establecimiento;
use App\Models\CabeceraMonitoreo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\DB as DBFacade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;

class UsuarioController extends Controller
{
    /**
     * DASHBOARD DEL USUARIO (Mapa de Progresión)
     * Muestra en qué etapa del proceso se encuentra cada establecimiento.
     * Sirve como Dashboard General.
     */
    public function index(Request $request)
    {
        $anioFiltro = $request->input('anio', 'todos');
        $modulos = \App\Helpers\ImplementacionHelper::getModulos();

        // Años disponibles
        $aniosCollector = collect([date('Y')]);
        $aniosMonitoreo = CabeceraMonitoreo::whereNotNull('fecha')->selectRaw('YEAR(fecha) as anio')->distinct()->pluck('anio');
        $aniosCollector = $aniosCollector->merge($aniosMonitoreo);
        
        foreach ($modulos as $mod) {
            $modelo = $mod['modelo'];
            $file = base_path(str_replace(['App\\', '\\'], ['app/', '/'], $modelo) . '.php');
            if (file_exists($file) && class_exists($modelo)) {
                $aniosMod = $modelo::whereNotNull('fecha')->selectRaw('YEAR(fecha) as anio')->distinct()->pluck('anio');
                $aniosCollector = $aniosCollector->merge($aniosMod);
            }
        }
        $aniosDisponibles = $aniosCollector->filter()->unique()->sortDesc()->values();

        // ── Contexto de Progresión ──────────────────────────────────────────
        $ctx = $this->getProgressionContext($anioFiltro);

        // ── Unificar en establecimientos con coordenadas ───────────────────
        $establecimientosMap = Establecimiento::whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get(['id', 'codigo', 'nombre', 'distrito', 'provincia', 'categoria', 'red', 'microred', 'latitud', 'longitud'])
            ->map(function ($est) use ($ctx) {
                $tieneMonitoreo = in_array($est->id, $ctx->idsConMonitoreo);
                $etapa          = $tieneMonitoreo ? 1 : 0; // 0 = Sin Diagnostico, 1 = Con Diagnostico Situacional

                $est->etapa            = $etapa;
                $est->tiene_monitoreo  = $tieneMonitoreo;
                $est->total_monitoreos = (int)($ctx->totalMonitoreoPorId[$est->id] ?? 0);
                return $est;
            });

        // ── Contadores por Diagnóstico Situacional ──────────────────────────────
        $contadores = [
            'total'           => $establecimientosMap->count(),
            'sin_diagnostico' => $establecimientosMap->where('etapa', 0)->count(),
            'con_diagnostico' => $establecimientosMap->where('etapa', 1)->count(),
        ];

        // ── Filtros ────────────────────────────────────────────────────────
        $baseFiltros = Establecimiento::whereNotNull('latitud')->whereNotNull('longitud');
        
        $provincias = (clone $baseFiltros)->whereNotNull('provincia')->distinct()->orderBy('provincia')->pluck('provincia');
        $redes      = (clone $baseFiltros)->whereNotNull('red')->distinct()->orderBy('red')->pluck('red');
        $microredes = (clone $baseFiltros)->whereNotNull('microred')->distinct()->orderBy('microred')->pluck('microred');
        $categorias = (clone $baseFiltros)->whereNotNull('categoria')->distinct()->orderBy('categoria')->pluck('categoria');
        $distritos  = (clone $baseFiltros)->whereNotNull('distrito')->distinct()->orderBy('distrito')->pluck('distrito');

        return view('usuario.dashboard.mapa_progresion', compact(
            'establecimientosMap',
            'contadores',
            'provincias',
            'redes',
            'microredes',
            'categorias',
            'distritos',
            'aniosDisponibles',
            'anioFiltro'
        ));
    }



    /**
     * Dashboard de Equipos de Cómputo
     */
    public function dashboardEquipos()
    {
        // Obtener años disponibles
        $aniosDisponibles = CabeceraMonitoreo::selectRaw('DISTINCT YEAR(fecha) as anio')
            ->orderBy('anio', 'desc')
            ->pluck('anio');

        // Obtener tipos de establecimiento
        $tipos = ['ESPECIALIZADO', 'NO ESPECIALIZADO'];

        // Obtener provincias (solo las que tienen equipos)
        $establecimientosConEquipos = \App\Models\EquipoComputo::select('cabecera_monitoreo_id')
            ->distinct()
            ->pluck('cabecera_monitoreo_id');

        $establecimientosIds = CabeceraMonitoreo::whereIn('id', $establecimientosConEquipos)
            ->pluck('establecimiento_id')
            ->unique();

        $provincias = Establecimiento::select('provincia')
            ->distinct()
            ->whereNotNull('provincia')
            ->whereIn('id', $establecimientosIds)
            ->orderBy('provincia')
            ->pluck('provincia');

        // Obtener establecimientos (solo los que tienen equipos)
        $establecimientos = Establecimiento::select('id', 'nombre', 'codigo')
            ->whereIn('id', $establecimientosIds)
            ->orderBy('nombre')
            ->get();

        // Obtener TODOS los módulos posibles (estándar y especializados)
        // Esto permite filtrar por módulos incluso si no tienen equipos registrados aún
        $todosLosModulos = \App\Helpers\ModuloHelper::getTodosLosModulos();

        $modulos = collect($todosLosModulos)->map(function ($nombre, $valor) {
            return [
                'valor' => $valor,
                'nombre' => $nombre
            ];
        })->values()->toArray();

        // Obtener descripciones (solo las que tienen equipos registrados)
        $descripciones = \App\Models\EquipoComputo::select('descripcion')
            ->distinct()
            ->whereNotNull('descripcion')
            ->whereHas('cabecera')
            ->orderBy('descripcion')
            ->pluck('descripcion');

        // Estadísticas iniciales (mes y año actual)
        $mesActual = now()->month;
        $anioActual = now()->year;

        $totalEquipos = \App\Models\EquipoComputo::whereHas('cabecera', function ($q) use ($mesActual, $anioActual) {
            $q->whereMonth('fecha', $mesActual)
                ->whereYear('fecha', $anioActual);
        })->count();

        return view('usuario.dashboard.dashboard_equipos', compact(
            'aniosDisponibles',
            'tipos',
            'provincias',
            'establecimientos',
            'modulos',
            'descripciones',
            'totalEquipos'
        ));
    }





    /**
     * GESTIÓN DE PERFIL
     */
    public function perfil()
    {
        $user = Auth::user();
        return view('usuario.perfil.perfil', compact('user'));
    }

    /**
     * ACTUALIZAR PERFIL
     */
    public function perfilUpdate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->fill($request->only('name', 'apellido_paterno', 'apellido_materno', 'email'));

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Tu perfil ha sido actualizado correctamente.');
    }

    /**
     * AJAX: Obtener estadísticas de equipos según filtros
     */
    public function getEquiposStats(Request $request)
    {
        try {
            \Illuminate\Support\Facades\Log::info('getEquiposStats called', $request->all());

            $mes = $request->input('mes');
            $anio = $request->input('anio');
            $tipo = $request->input('tipo');
            $provincia = $request->input('provincia');
            $distrito = $request->input('distrito');
            $establecimientoId = $request->input('establecimiento_id');
            $modulo = $request->input('modulo');
            $descripcion = $request->input('descripcion');

            // Códigos y nombres de establecimientos especializados
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

            // Construir query base
            $query = \App\Models\EquipoComputo::query();

            // Filtros de fecha
            if ($mes && $anio) {
                $query->whereHas('cabecera', function ($q) use ($mes, $anio) {
                    $q->whereMonth('fecha', $mes)->whereYear('fecha', $anio);
                });
                $periodoTexto = $this->getNombreMes($mes) . ' ' . $anio;
            } elseif ($mes) {
                $query->whereHas('cabecera', function ($q) use ($mes) {
                    $q->whereMonth('fecha', $mes);
                });
                $periodoTexto = $this->getNombreMes($mes) . ' (Todos los años)';
            } elseif ($anio) {
                $query->whereHas('cabecera', function ($q) use ($anio) {
                    $q->whereYear('fecha', $anio);
                });
                $periodoTexto = 'Año ' . $anio;
            } else {
                $periodoTexto = 'Todos los períodos';
            }

            // Filtro por Tipo de Establecimiento
            if ($tipo) {
                if ($tipo === 'ESPECIALIZADO') {
                    $query->whereHas('cabecera.establecimiento', function ($q) use ($codigosCSMC, $nombresCSMC) {
                        $q->where(function ($subQ) use ($codigosCSMC, $nombresCSMC) {
                            $subQ->whereIn('codigo', $codigosCSMC)
                                ->orWhereIn(DB::raw('UPPER(TRIM(nombre))'), $nombresCSMC);
                        });
                    });
                } elseif ($tipo === 'NO ESPECIALIZADO') {
                    $query->whereHas('cabecera.establecimiento', function ($q) use ($codigosCSMC, $nombresCSMC) {
                        $q->whereNotIn('codigo', $codigosCSMC)
                            ->whereNotIn(DB::raw('UPPER(TRIM(nombre))'), $nombresCSMC);
                    });
                }
            }

            // Filtro por Provincia
            if ($provincia) {
                $query->whereHas('cabecera.establecimiento', function ($q) use ($provincia) {
                    $q->where('provincia', $provincia);
                });
            }

            // Filtro por Distrito
            if ($distrito) {
                $query->whereHas('cabecera.establecimiento', function ($q) use ($distrito) {
                    $q->where('distrito', $distrito);
                });
            }

            // Filtro por Establecimiento
            if ($establecimientoId) {
                $query->whereHas('cabecera', function ($q) use ($establecimientoId) {
                    $q->where('establecimiento_id', $establecimientoId);
                });
            }

            // Filtro por Módulo (puede ser múltiple)
            $modulos = $request->input('modulos', []);
            if (!empty($modulos) && is_array($modulos)) {
                $query->whereIn('modulo', $modulos);
            }

            // Filtro por Descripción
            if ($descripcion) {
                $query->where('descripcion', $descripcion);
            }

            // Total de equipos
            $totalEquipos = (clone $query)->count();
            \Illuminate\Support\Facades\Log::info('Total equipos encontrados: ' . $totalEquipos);

            // Equipos por Estado
            $equiposPorEstado = (clone $query)
                ->select('estado', \DB::raw('count(*) as total'))
                ->groupBy('estado')
                ->pluck('total', 'estado')
                ->toArray();

            // Equipos por Tipo de Establecimiento
            $queryEspecializados = clone $query;
            $equiposEspecializados = $queryEspecializados->whereHas('cabecera.establecimiento', function ($q) use ($codigosCSMC, $nombresCSMC) {
                $q->where(function ($subQ) use ($codigosCSMC, $nombresCSMC) {
                    $subQ->whereIn('codigo', $codigosCSMC)
                        ->orWhereIn(DB::raw('UPPER(TRIM(nombre))'), $nombresCSMC);
                });
            })->count();

            $queryNoEspecializados = clone $query;
            $equiposNoEspecializados = $queryNoEspecializados->whereHas('cabecera.establecimiento', function ($q) use ($codigosCSMC, $nombresCSMC) {
                $q->whereNotIn('codigo', $codigosCSMC)
                    ->whereNotIn(DB::raw('UPPER(TRIM(nombre))'), $nombresCSMC);
            })->count();

            $equiposPorTipo = [
                'ESPECIALIZADO' => $equiposEspecializados,
                'NO ESPECIALIZADO' => $equiposNoEspecializados
            ];

            // Equipos por Módulo
            $equiposPorModulo = (clone $query)
                ->select('modulo', DB::raw('count(*) as total'))
                ->whereNotNull('modulo')
                ->groupBy('modulo')
                ->orderBy('total', 'desc')
                ->get()
                ->mapWithKeys(function ($item) {
                    $nombreAmigable = \App\Helpers\ModuloHelper::getNombreAmigable($item->modulo);
                    return [($nombreAmigable ?? $item->modulo) => $item->total];
                })
                ->toArray();

            // Todas las Descripciones (sin límite Top 10)
            $topDescripciones = (clone $query)
                ->select('descripcion', DB::raw('count(*) as total'))
                ->whereNotNull('descripcion')
                ->groupBy('descripcion')
                ->orderBy('total', 'desc')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->descripcion => $item->total];
                })
                ->toArray();

            // Equipos por Establecimiento - Query robusto
            $queryEstablecimientos = \DB::table('mon_equipos_computo')
                ->join('mon_cabecera_monitoreo', 'mon_equipos_computo.cabecera_monitoreo_id', '=', 'mon_cabecera_monitoreo.id')
                ->join('establecimientos', 'mon_cabecera_monitoreo.establecimiento_id', '=', 'establecimientos.id');

            // Aplicar filtros de fecha
            if ($mes && $anio) {
                $queryEstablecimientos->whereMonth('mon_cabecera_monitoreo.fecha', $mes)
                    ->whereYear('mon_cabecera_monitoreo.fecha', $anio);
            } elseif ($mes) {
                $queryEstablecimientos->whereMonth('mon_cabecera_monitoreo.fecha', $mes);
            } elseif ($anio) {
                $queryEstablecimientos->whereYear('mon_cabecera_monitoreo.fecha', $anio);
            }

            // Aplicar filtro de tipo
            if ($tipo) {
                if ($tipo === 'ESPECIALIZADO') {
                    $queryEstablecimientos->whereIn('establecimientos.codigo', $codigosCSMC);
                } else {
                    $queryEstablecimientos->whereNotIn('establecimientos.codigo', $codigosCSMC);
                }
            }

            // Aplicar filtro de provincia
            if ($provincia) {
                $queryEstablecimientos->where('establecimientos.provincia', $provincia);
            }

            // Aplicar filtro de establecimiento
            if ($establecimientoId) {
                $queryEstablecimientos->where('establecimientos.id', $establecimientoId);
            }

            // Aplicar filtro de módulo
            if ($modulo) {
                $queryEstablecimientos->where('mon_equipos_computo.modulo', $modulo);
            }

            // Aplicar filtro de descripción
            if ($descripcion) {
                $queryEstablecimientos->where('mon_equipos_computo.descripcion', $descripcion);
            }

            // Group by ID y Nombre para SQL strict mode y evitar errores de ambigüedad
            $equiposPorEstablecimiento = $queryEstablecimientos
                ->select('establecimientos.nombre', \DB::raw('count(mon_equipos_computo.id) as total'))
                ->groupBy('establecimientos.id', 'establecimientos.nombre')
                ->orderByDesc('total')
                ->pluck('total', 'nombre')
                ->toArray();

            // --- NUEVO: Estadísticas de Conectividad para Gráficos ---
            $equiposPorConectividad = [];
            $equiposPorFuenteWifi = [];
            $equiposPorProveedor = [];

            // Obtenemos todos los equipos filtrados con sus cabeceras y detalles
            $equiposParaStats = (clone $query)->with(['cabecera.detalles'])->get();

            foreach ($equiposParaStats as $e) {
                $con = \App\Helpers\ModuloHelper::getConectividadActa($e->cabecera);

                // Conectividad
                $tipo = $con['tipo'];
                $equiposPorConectividad[$tipo] = ($equiposPorConectividad[$tipo] ?? 0) + ($e->cantidad ?? 1);

                // Fuente WiFi (solo si tiene algo)
                $fuente = $con['fuente'];
                if ($fuente && $fuente !== '---') {
                    $equiposPorFuenteWifi[$fuente] = ($equiposPorFuenteWifi[$fuente] ?? 0) + ($e->cantidad ?? 1);
                }

                // Proveedor (solo si tiene algo)
                $operador = $con['operador'];
                if ($operador && $operador !== '---') {
                    $equiposPorProveedor[$operador] = ($equiposPorProveedor[$operador] ?? 0) + ($e->cantidad ?? 1);
                }
            }

            // Retornar datos
            $response = [
                'totalEquipos' => $totalEquipos,
                'periodoTexto' => $periodoTexto,
                'equiposPorEstado' => $equiposPorEstado,
                'equiposPorTipo' => $equiposPorTipo,
                'equiposPorModulo' => $equiposPorModulo,
                'topDescripciones' => $topDescripciones,
                'equiposPorEstablecimiento' => $equiposPorEstablecimiento,
                'equiposPorConectividad' => $equiposPorConectividad,
                'equiposPorFuenteWifi' => $equiposPorFuenteWifi,
                'equiposPorProveedor' => $equiposPorProveedor
            ];

            \Illuminate\Support\Facades\Log::info('DEBUG DATA:', [
                'totalEquipos' => $totalEquipos,
                'count_estado' => count($equiposPorEstado),
                'count_tipo' => count($equiposPorTipo),
                'count_modulo' => count($equiposPorModulo),
                'count_desc' => count($topDescripciones),
                'count_estab' => count($equiposPorEstablecimiento),
                'sample_estado' => $equiposPorEstado,
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getEquiposStats: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());

            return response()->json([
                'error' => $e->getMessage(),
                'totalEquipos' => 0,
                'periodoTexto' => 'Error',
                'equiposPorEstado' => [],
                'equiposPorTipo' => ['ESPECIALIZADO' => 0, 'NO ESPECIALIZADO' => 0],
                'equiposPorModulo' => [],
                'topDescripciones' => []
            ], 500);
        }
    }

    /**
     * AJAX: Obtener opciones de filtros según selección actual
     */
    public function getFilterOptions(Request $request)
    {
        try {
            $mes = $request->input('mes');
            $anio = $request->input('anio');
            $tipo = $request->input('tipo');
            $provincia = $request->input('provincia');
            $distrito = $request->input('distrito');
            $establecimientoId = $request->input('establecimiento_id');
            $modulos = $request->input('modulos', []);

            // Códigos y nombres de establecimientos especializados
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

            // Construir query base
            $query = \App\Models\EquipoComputo::query();

            // Aplicar filtros de fecha
            if ($mes && $anio) {
                $query->whereHas('cabecera', function ($q) use ($mes, $anio) {
                    $q->whereMonth('fecha', $mes)->whereYear('fecha', $anio);
                });
            } elseif ($mes) {
                $query->whereHas('cabecera', function ($q) use ($mes) {
                    $q->whereMonth('fecha', $mes);
                });
            } elseif ($anio) {
                $query->whereHas('cabecera', function ($q) use ($anio) {
                    $q->whereYear('fecha', $anio);
                });
            }

            // Aplicar filtro por Tipo de Establecimiento
            if ($tipo) {
                if ($tipo === 'ESPECIALIZADO') {
                    $query->whereHas('cabecera.establecimiento', function ($q) use ($codigosCSMC, $nombresCSMC) {
                        $q->where(function ($subQ) use ($codigosCSMC, $nombresCSMC) {
                            $subQ->whereIn('codigo', $codigosCSMC)
                                ->orWhereIn(DB::raw('UPPER(TRIM(nombre))'), $nombresCSMC);
                        });
                    });
                } elseif ($tipo === 'NO ESPECIALIZADO') {
                    $query->whereHas('cabecera.establecimiento', function ($q) use ($codigosCSMC, $nombresCSMC) {
                        $q->whereNotIn('codigo', $codigosCSMC)
                            ->whereNotIn(DB::raw('UPPER(TRIM(nombre))'), $nombresCSMC);
                    });
                }
            }

            // Aplicar filtro por Provincia
            if ($provincia) {
                $query->whereHas('cabecera.establecimiento', function ($q) use ($provincia) {
                    $q->where('provincia', $provincia);
                });
            }

            // Aplicar filtro por Distrito
            if ($distrito) {
                $query->whereHas('cabecera.establecimiento', function ($q) use ($distrito) {
                    $q->where('distrito', $distrito);
                });
            }

            // Aplicar filtro por Establecimiento
            if ($establecimientoId) {
                $query->whereHas('cabecera', function ($q) use ($establecimientoId) {
                    $q->where('establecimiento_id', $establecimientoId);
                });
            }

            // Aplicar filtro por Módulos
            if (!empty($modulos) && is_array($modulos)) {
                $query->whereIn('modulo', $modulos);
            }

            // Obtener IDs de cabeceras y establecimientos con equipos filtrados
            $cabecerasIds = (clone $query)->pluck('cabecera_monitoreo_id')->unique();
            $establecimientosIds = CabeceraMonitoreo::whereIn('id', $cabecerasIds)
                ->pluck('establecimiento_id')
                ->unique();

            // Obtener provincias disponibles
            $provincias = Establecimiento::select('provincia')
                ->distinct()
                ->whereNotNull('provincia')
                ->whereIn('id', $establecimientosIds)
                ->orderBy('provincia')
                ->pluck('provincia')
                ->values();

            // Obtener distritos disponibles (filtrados por provincia si aplica)
            $distritosQuery = Establecimiento::select('distrito')
                ->distinct()
                ->whereNotNull('distrito')
                ->whereIn('id', $establecimientosIds)
                ->orderBy('distrito');
            if ($provincia) {
                $distritosQuery->where('provincia', $provincia);
            }
            $distritos = $distritosQuery->pluck('distrito')->values();

            // Obtener establecimientos disponibles
            $establecimientos = Establecimiento::select('id', 'nombre', 'codigo')
                ->whereIn('id', $establecimientosIds)
                ->orderBy('nombre')
                ->get()
                ->map(function ($est) {
                    return [
                        'id' => $est->id,
                        'nombre' => $est->nombre,
                        'codigo' => $est->codigo
                    ];
                })
                ->values();

            // Obtener módulos disponibles (de equipos filtrados)
            $modulosDisponibles = (clone $query)
                ->select('modulo')
                ->distinct()
                ->whereNotNull('modulo')
                ->orderBy('modulo')
                ->pluck('modulo')
                ->map(function ($modulo) {
                    return [
                        'valor' => $modulo,
                        'nombre' => \App\Helpers\ModuloHelper::getNombreAmigable($modulo) ?? $modulo
                    ];
                })
                ->values();

            // Obtener descripciones disponibles
            $descripciones = (clone $query)
                ->select('descripcion')
                ->distinct()
                ->whereNotNull('descripcion')
                ->orderBy('descripcion')
                ->pluck('descripcion')
                ->values();

            return response()->json([
                'success' => true,
                'provincias' => $provincias,
                'distritos' => $distritos,
                'establecimientos' => $establecimientos,
                'modulos' => $modulosDisponibles,
                'descripciones' => $descripciones
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getFilterOptions: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getNombreMes($numeroMes)
    {
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];
        return $meses[$numeroMes] ?? '';
    }



    /**
     * Centraliza el cálculo de datos para un item de programación.
     */
    private function formatProgramacionItem($p, $ctx, $establecimientosIdx)
    {
        $est = $p->establecimiento_id ? ($establecimientosIdx[$p->establecimiento_id] ?? null) : null;

        $lat = null; $lon = null;
        $tieneImpl = false; $tieneAsist = false; $tieneMonitoreo = false;
        $etapa = 0; $modulos = []; $totalImpl = 0; $totalAsist = 0; $totalMon = 0;
        $nombreDisplay = $p->nombre_pdf;
        $distrito = null; $red = null; $microred = null; $categoria = null; $codigo = null;
        $provincia = $p->provincia;

        if ($est) {
            $lat = (float) $est->latitud;
            $lon = (float) $est->longitud;
            if (abs($lat) > 180) $lat /= 100000000;
            if (abs($lon) > 180) $lon /= 100000000;

            $nombreDisplay = $est->nombre;
            $provincia     = $est->provincia ?? $p->provincia;
            $distrito      = $est->distrito;
            $red           = $est->red;
            $microred      = $est->microred;
            $categoria     = $est->categoria;
            $codigo        = $est->codigo;

            $tieneImpl      = $ctx->codigosConImplementacion->contains($est->codigo);
            $tieneAsist     = in_array($est->id, $ctx->idsConAsistencia);
            $tieneMonitoreo = in_array($est->id, $ctx->idsConMonitoreo);

            if ($tieneImpl && $tieneAsist && $tieneMonitoreo) $etapa = 4;
            elseif ($tieneMonitoreo) $etapa = 3;
            elseif ($tieneAsist)     $etapa = 2;
            elseif ($tieneImpl)      $etapa = 1;

            $modulos    = $ctx->modulosPorCodigo[$est->codigo] ?? [];
            $totalImpl  = count($modulos);
            $totalAsist = (int)($ctx->totalAsistenciaPorId[$est->id] ?? 0);
            $totalMon   = (int)($ctx->totalMonitoreoPorId[$est->id] ?? 0);
        }

        return [
            'id'              => $p->id,
            'nombre_pdf'      => $p->nombre_pdf,
            'nombre'          => $nombreDisplay,
            'provincia'       => $provincia,
            'provincia_pdf'   => $p->provincia,
            'sector'          => $p->sector,
            'cuadril'         => $p->cuadril,
            'comienzo'        => $p->comienzo ? $p->comienzo->format('d/m/Y') : null,
            'fin'             => $p->fin      ? $p->fin->format('d/m/Y')      : null,
            'comienzo_iso'    => $p->comienzo ? $p->comienzo->format('Y-m-d') : null,
            'fin_iso'         => $p->fin      ? $p->fin->format('Y-m-d')      : null,
            'dias'            => $p->dias,
            'lat'             => $lat && !is_nan($lat) ? $lat : null,
            'lon'             => $lon && !is_nan($lon) ? $lon : null,
            'tiene_est'       => !!$est,
            'establecimiento_id' => $p->establecimiento_id,
            'distrito'        => $distrito,
            'red'             => $red,
            'microred'        => $microred,
            'categoria'       => $categoria,
            'codigo'          => $codigo,
            'etapa'           => $etapa,
            'tiene_impl'      => $tieneImpl,
            'tiene_asist'     => $tieneAsist,
            'tiene_monitoreo' => $tieneMonitoreo,
            'modulos_impl'    => $modulos,
            'total_impl'      => $totalImpl,
            'total_asistencias' => $totalAsist,
            'total_monitoreos'  => $totalMon,
        ];
    }

    /**
     * Obtiene el contexto de progresión de todos los establecimientos.
     * Centraliza la lógica para evitar redundancia y resolver problemas de inferencia de tipos.
     */
    private function getProgressionContext($anioFiltro = 'todos')
    {
        $queryMon = CabeceraMonitoreo::where(function($q) {
            $q->where('anulado', 0)->orWhereNull('anulado');
        });
        if ($anioFiltro !== 'todos') {
            $queryMon->whereYear('fecha', '<=', $anioFiltro);
        }

        $idsConMonitoreo = (clone $queryMon)->distinct()->pluck('establecimiento_id')->toArray();
        $totalMonitoreoPorId = (clone $queryMon)->select('establecimiento_id', DB::raw('count(*) as total'))
            ->groupBy('establecimiento_id')->pluck('total', 'establecimiento_id');

        return (object) [
            'idsConMonitoreo'     => $idsConMonitoreo,
            'totalMonitoreoPorId' => $totalMonitoreoPorId
        ];
    }
}