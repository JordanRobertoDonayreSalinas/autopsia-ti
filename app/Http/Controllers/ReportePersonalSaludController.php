<?php

namespace App\Http\Controllers;

use App\Exports\PersonalSaludExport;
use App\Models\Establecimiento;
use App\Models\MonitoreoModulos;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reporte de Personal de Salud (RR.HH): un renglón por cada trabajador
 * registrado en el módulo RR.HH de cada acta (contenido['trabajadores']),
 * a diferencia del reporte legado "Consultorios de Medicina" que solo
 * cubría al profesional entrevistado en el módulo fijo consulta_medicina.
 */
class ReportePersonalSaludController extends Controller
{
    public function index(Request $request)
    {
        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            session(['personal_salud_fecha_inicio' => $request->input('fecha_inicio')]);
            session(['personal_salud_fecha_fin' => $request->input('fecha_fin')]);
        }

        $fechaInicio = $request->input('fecha_inicio', session('personal_salud_fecha_inicio', now()->startOfYear()->format('Y-m-d')));
        $fechaFin = $request->input('fecha_fin', session('personal_salud_fecha_fin', now()->format('Y-m-d')));

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

        $servicios = collect(['MEDICINA', 'ODONTOLOGÍA', 'ENFERMERÍA', 'OBSTETRICIA', 'PSICOLOGÍA', 'NUTRICIÓN', 'FARMACIA', 'LABORATORIO', 'TRIAJE', 'URGENCIAS Y EMERGENCIAS', 'TÓPICO', 'CRED', 'INMUNIZACIONES', 'ADMISIÓN Y ARCHIVO', 'GESTIÓN ADMINISTRATIVA', 'OTROS']);

        $modulos = $this->buildQuery($request, $fechaInicio, $fechaFin)->get();
        $filas = $this->construirFilas($modulos, $request);

        $page = (int) $request->input('page', 1);
        $porPagina = 25;
        $total = $filas->count();
        $items = $filas->slice(($page - 1) * $porPagina, $porPagina)->values();

        $trabajadores = new LengthAwarePaginator($items, $total, $porPagina, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        $totalConDnie = $filas->filter(fn($f) => $f['trabajador']['tiene_dnie'] === 'SI')->count();
        $totalSerums = $filas->filter(fn($f) => $f['trabajador']['es_serums'] === 'SI')->count();
        $totalSinColegiatura = $filas->filter(fn($f) => empty($f['trabajador']['colegiatura']))->count();

        return view('usuario.reportes.personal_salud', compact(
            'trabajadores', 'establecimientos', 'distritos', 'provincias', 'servicios',
            'fechaInicio', 'fechaFin', 'total', 'totalConDnie', 'totalSerums', 'totalSinColegiatura'
        ));
    }

    public function exportarExcel(Request $request)
    {
        $modulos = $this->buildQuery(
            $request,
            $request->input('fecha_inicio'),
            $request->input('fecha_fin')
        )->get();

        $filas = $this->construirFilas($modulos, $request);

        $filename = 'Reporte_Personal_Salud_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new PersonalSaludExport($filas), $filename);
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
     * Consulta base: todos los módulos RR.HH dentro del rango de fechas y
     * filtros solicitados (uno por acta; cada uno trae su propia lista de
     * trabajadores en contenido['trabajadores']).
     */
    private function buildQuery(Request $request, $fechaInicio, $fechaFin)
    {
        $query = MonitoreoModulos::with('cabecera.establecimiento')
            ->where('modulo_nombre', 'rrhh');

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

        return $query;
    }

    /**
     * A partir de los módulos RR.HH obtenidos, arma una fila por cada
     * trabajador (aplicando los filtros de servicio/profesión, que son
     * datos calculados dentro del JSON y no columnas de BD filtrables).
     */
    private function construirFilas($modulos, Request $request)
    {
        $filas = collect();

        foreach ($modulos as $modulo) {
            $contenido = is_array($modulo->contenido) ? $modulo->contenido : [];
            $trabajadores = $contenido['trabajadores'] ?? [];
            if (!is_array($trabajadores)) {
                continue;
            }

            foreach ($trabajadores as $t) {
                if (empty($t['nombres']) && empty($t['apellido_paterno']) && empty($t['doc'])) {
                    continue;
                }

                if ($request->filled('servicio') && strtoupper($t['servicio'] ?? '') !== strtoupper($request->servicio)) {
                    continue;
                }

                $filas->push([
                    'establecimiento' => $modulo->cabecera->establecimiento,
                    'fecha' => $modulo->cabecera->fecha,
                    'numeroActa' => $modulo->cabecera->numero_acta ?? $modulo->cabecera->id,
                    'trabajador' => $t,
                ]);
            }
        }

        return $filas;
    }

    /**
     * Helper de filtro por tipo de establecimiento (ESPECIALIZADO/NO
     * ESPECIALIZADO), mismo criterio que los demás reportes.
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
