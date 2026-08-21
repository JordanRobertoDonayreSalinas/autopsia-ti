<?php

namespace App\Http\Controllers;

use App\Exports\ConsultoriosInfraExport;
use App\Exports\RequerimientosEquiposExport;
use App\Helpers\ModuloHelper;
use App\Models\Establecimiento;
use App\Models\MonitoreoModulos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReporteConsultoriosController extends Controller
{
    /**
     * Módulos fijos que no son consultorios dinámicos (no aplican a este reporte).
     */
    private const MODULOS_FIJOS = ['infraestructura_2d', 'rrhh', 'config_modulos'];

    /**
     * Muestra el reporte de Consultorios: infraestructura (electricidad,
     * tomas, punto de red, conectividad) y requerimientos de equipos, un
     * renglón por consultorio dinámico registrado.
     */
    public function index(Request $request)
    {
        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            session(['consultorios_fecha_inicio' => $request->input('fecha_inicio')]);
            session(['consultorios_fecha_fin' => $request->input('fecha_fin')]);
        }

        $fechaInicio = $request->input('fecha_inicio', session('consultorios_fecha_inicio', now()->startOfYear()->format('Y-m-d')));
        $fechaFin = $request->input('fecha_fin', session('consultorios_fecha_fin', now()->format('Y-m-d')));

        $provincias = Establecimiento::whereIn('id', function ($q) {
            $q->select('establecimiento_id')->from('mon_cabecera_monitoreo');
        })->distinct()->pluck('provincia')->filter()->sort()->values();

        $distritos = collect();
        if ($request->filled('provincia')) {
            $distritos = Establecimiento::where('provincia', $request->provincia)
                ->whereIn('id', function ($q) {
                    $q->select('establecimiento_id')->from('mon_cabecera_monitoreo');
                })
                ->distinct()->pluck('distrito')->filter()->sort()->values();
        }

        $establecimientosQuery = Establecimiento::whereIn('id', function ($q) {
            $q->select('establecimiento_id')->from('mon_cabecera_monitoreo');
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
        $establecimientos = $establecimientosQuery->orderBy('nombre')->get(['id', 'nombre']);

        $vista = $request->input('vista') === 'requerimientos' ? 'requerimientos' : 'infraestructura';

        $query = $this->buildQuery($request, $fechaInicio, $fechaFin);
        $modulos = $query->orderBy('created_at', 'desc')->get();

        // Las alertas (infraestructura) y las filas de requerimientos (una por
        // ítem, no por consultorio) son datos calculados que no se pueden
        // filtrar/paginar en SQL directamente: se resuelven todos los
        // resultados filtrados y se pagina la colección ya calculada, para
        // que el total y las páginas mostradas sean exactos.
        if ($vista === 'requerimientos') {
            $filas = $this->construirFilasRequerimientos($modulos);
            $consultorios = $this->paginarColeccion($filas, $request);
        } else {
            $enriquecidos = $modulos->map(fn($m) => $this->enriquecerModulo($m));
            if ($request->filled('solo_alertas')) {
                $enriquecidos = $enriquecidos->filter(fn($c) => count($c['alertas']) > 0)->values();
            }
            $consultorios = $this->paginarColeccion($enriquecidos, $request);
        }

        return view('usuario.reportes.consultorios', compact(
            'consultorios', 'establecimientos', 'distritos', 'provincias', 'fechaInicio', 'fechaFin', 'vista'
        ));
    }

    /** Pagina en PHP una colección ya calculada, preservando los filtros en la URL. */
    private function paginarColeccion($coleccion, Request $request, int $porPagina = 20)
    {
        $page = (int) $request->input('page', 1);
        $total = $coleccion->count();
        $items = $coleccion->slice(($page - 1) * $porPagina, $porPagina)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator($items, $total, $porPagina, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }

    /**
     * Exporta a Excel la infraestructura de los consultorios filtrados.
     */
    public function exportarExcel(Request $request)
    {
        $query = $this->buildQuery(
            $request,
            $request->input('fecha_inicio'),
            $request->input('fecha_fin')
        );

        $modulos = $query->get()->map(fn($modulo) => $this->enriquecerModulo($modulo));

        if ($request->filled('solo_alertas')) {
            $modulos = $modulos->filter(fn($c) => count($c['alertas']) > 0)->values();
        }

        $filename = 'Reporte_Consultorios_Infraestructura_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new ConsultoriosInfraExport($modulos), $filename);
    }

    /**
     * Exporta a Excel el requerimiento de equipos (lo que cada consultorio
     * necesita y todavía no tiene) de los consultorios filtrados.
     */
    public function exportarRequerimientosExcel(Request $request)
    {
        $query = $this->buildQuery(
            $request,
            $request->input('fecha_inicio'),
            $request->input('fecha_fin')
        );

        $filas = $this->construirFilasRequerimientos($query->get());

        $filename = 'Reporte_Consultorios_Requerimientos_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new RequerimientosEquiposExport($filas), $filename);
    }

    /**
     * A partir de una colección de módulos (consultorios), arma una fila por
     * cada requerimiento de equipo pendiente (resolviendo primero de qué
     * slug leerlos, por si el consultorio comparte equipo con su físico).
     */
    private function construirFilasRequerimientos($modulos)
    {
        $filas = collect();

        foreach ($modulos as $modulo) {
            $contenido = is_array($modulo->contenido) ? $modulo->contenido : [];
            $slugEquipos = ModuloHelper::getSlugEquiposEfectivo($contenido, $modulo->modulo_nombre);
            $datosConsultorio = ModuloHelper::getDatosConsultorio($modulo->cabecera, $modulo->modulo_nombre);

            $slugEquiposUpper = strtoupper($slugEquipos);
            $requerimientos = $modulo->cabecera->requerimientos
                ->filter(fn($r) => strtoupper($r->modulo) === $slugEquiposUpper);

            foreach ($requerimientos as $req) {
                $filas->push([
                    'establecimiento' => $modulo->cabecera->establecimiento,
                    'fecha' => $modulo->cabecera->fecha,
                    'modulo' => $modulo->modulo_nombre,
                    'titulo_consultorio' => $contenido['titulo_consultorio'] ?? $modulo->modulo_nombre,
                    'datosConsultorio' => $datosConsultorio,
                    'requerimiento' => $req,
                ]);
            }
        }

        return $filas;
    }

    /** Establecimientos filtrados por provincia (AJAX, cascada de filtros) */
    public function getEstablecimientos(Request $request)
    {
        $query = Establecimiento::whereIn('id', function ($q) {
            $q->select('establecimiento_id')->from('mon_cabecera_monitoreo');
        });
        if ($request->filled('provincia')) {
            $query->where('provincia', $request->provincia);
        }
        if ($request->filled('distrito')) {
            $query->where('distrito', $request->distrito);
        }
        if ($request->filled('tipo')) {
            $this->applyTipoFilter($query, $request->tipo);
        }

        return response()->json($query->orderBy('nombre')->get(['id', 'nombre']));
    }

    /** Distritos filtrados por provincia (AJAX, cascada de filtros) */
    public function ajaxGetDistritos(Request $request)
    {
        $query = Establecimiento::whereIn('id', function ($q) {
            $q->select('establecimiento_id')->from('mon_cabecera_monitoreo');
        });
        if ($request->filled('provincia')) {
            $query->where('provincia', $request->provincia);
        }
        if ($request->filled('tipo')) {
            $this->applyTipoFilter($query, $request->tipo);
        }

        return response()->json($query->distinct()->pluck('distrito')->filter()->sort()->values());
    }

    /**
     * Construye la consulta base: todos los consultorios dinámicos (no
     * módulos fijos) dentro del rango de fechas y filtros solicitados.
     */
    private function buildQuery(Request $request, $fechaInicio, $fechaFin)
    {
        $query = MonitoreoModulos::with(['cabecera.establecimiento', 'cabecera.detalles', 'cabecera.equipos', 'cabecera.requerimientos'])
            ->whereNotIn('modulo_nombre', self::MODULOS_FIJOS);

        $query->whereHas('cabecera', function ($q) use ($fechaInicio, $fechaFin, $request) {
            if ($fechaInicio) {
                $q->whereDate('fecha', '>=', $fechaInicio);
            }
            if ($fechaFin) {
                $q->whereDate('fecha', '<=', $fechaFin);
            }
            if ($request->filled('establecimiento_id')) {
                $q->where('establecimiento_id', $request->establecimiento_id);
            }
            if ($request->filled('provincia') || $request->filled('distrito') || $request->filled('tipo')) {
                $q->whereHas('establecimiento', function ($q2) use ($request) {
                    if ($request->filled('provincia')) {
                        $q2->where('provincia', $request->provincia);
                    }
                    if ($request->filled('distrito')) {
                        $q2->where('distrito', $request->distrito);
                    }
                    if ($request->filled('tipo')) {
                        $this->applyTipoFilter($q2, $request->tipo, '');
                    }
                });
            }
        });

        if ($request->filled('tipo_consultorio')) {
            $tipoConsultorio = strtoupper($request->tipo_consultorio);
            $query->where(function ($q) use ($tipoConsultorio) {
                if ($tipoConsultorio === 'FUNCIONAL') {
                    $q->whereRaw("UPPER(JSON_UNQUOTE(JSON_EXTRACT(contenido, '$.tipo_consultorio'))) = 'FUNCIONAL'");
                } else {
                    $q->whereRaw("(JSON_EXTRACT(contenido, '$.tipo_consultorio') IS NULL OR UPPER(JSON_UNQUOTE(JSON_EXTRACT(contenido, '$.tipo_consultorio'))) != 'FUNCIONAL')");
                }
            });
        }

        return $query;
    }

    /**
     * Enriquece un registro de MonitoreoModulos con todos los datos
     * resueltos que necesita el reporte: servicio/departamento, tipo y
     * vinculación, infraestructura efectiva (heredada si aplica),
     * conectividad, conteo de equipos/requerimientos y alertas.
     */
    private function enriquecerModulo(MonitoreoModulos $modulo): array
    {
        $cabecera = $modulo->cabecera;
        $contenido = is_array($modulo->contenido) ? $modulo->contenido : [];

        $datosConsultorio = ModuloHelper::getDatosConsultorio($cabecera, $modulo->modulo_nombre);
        $contenidoEfectivo = ModuloHelper::getContenidoEfectivo($cabecera, $modulo->modulo_nombre);
        $conectividad = ModuloHelper::getConectividadActa($cabecera, $modulo->modulo_nombre);
        $slugEquipos = ModuloHelper::getSlugEquiposEfectivo($contenido, $modulo->modulo_nombre);

        // EquipoComputo/EquipoRequerimiento uppercasean 'modulo' al leerlo
        // (App\Traits\UppercaseAttributes), pero MonitoreoModulos.modulo_nombre
        // se guarda tal cual (slug en minúsculas): comparar en mayúsculas de
        // ambos lados evita que el filtro en memoria no encuentre nada.
        $slugEquiposUpper = strtoupper($slugEquipos);
        $cantidadEquipos = $cabecera && $cabecera->equipos
            ? $cabecera->equipos->filter(fn($e) => strtoupper($e->modulo) === $slugEquiposUpper)->sum('cantidad')
            : 0;
        $cantidadRequerimientos = $cabecera && $cabecera->requerimientos
            ? $cabecera->requerimientos->filter(fn($r) => strtoupper($r->modulo) === $slugEquiposUpper)->count()
            : 0;

        $alertas = [];
        if (strtoupper($contenidoEfectivo['cuenta_electricidad'] ?? 'SI') === 'NO') {
            $alertas[] = 'SIN ELECTRICIDAD';
        }
        if (strtoupper($conectividad['tipo'] ?? '') === 'SIN CONECTIVIDAD') {
            $alertas[] = 'SIN CONECTIVIDAD';
        }
        if ($cantidadEquipos == 0) {
            $alertas[] = 'SIN EQUIPOS DE CÓMPUTO';
        }
        if (strtoupper($contenidoEfectivo['requiere_mas_puntos_red'] ?? 'NO') === 'SI') {
            $alertas[] = 'REQUIERE MÁS PUNTOS DE RED';
        }
        if ($cantidadRequerimientos > 0) {
            $alertas[] = $cantidadRequerimientos . ' REQUERIMIENTO(S) DE EQUIPO PENDIENTE(S)';
        }

        return [
            'modulo' => $modulo,
            'cabecera' => $cabecera,
            'contenido' => $contenido,
            'contenidoEfectivo' => $contenidoEfectivo,
            'datosConsultorio' => $datosConsultorio,
            'conectividad' => $conectividad,
            'cantidadEquipos' => $cantidadEquipos,
            'cantidadRequerimientos' => $cantidadRequerimientos,
            'alertas' => $alertas,
        ];
    }

    /**
     * Helper de filtro por tipo de establecimiento (ESPECIALIZADO/NO
     * ESPECIALIZADO), mismo criterio que ReporteEquiposController.
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
            'CSMC MENTE SANA',
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
}
