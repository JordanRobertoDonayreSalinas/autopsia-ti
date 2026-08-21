<?php

namespace App\Http\Controllers;

use App\Helpers\ModuloHelper;
use App\Models\Establecimiento;
use App\Models\MonitoreoModulos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Panel de Indicadores: gráficos consolidados de lo que hoy no cubre el
 * dashboard de Equipos de Cómputo — consultorios (físico/funcional y
 * alertas de infraestructura), conectividad, RR.HH./personal de salud, y
 * auditoría de calidad de datos (inconsistencias y duplicidad de equipos).
 * Reutiliza los mismos criterios ya usados en ReporteConsultoriosController,
 * ReportePersonalSaludController, AuditoriaEquiposController y
 * AuditoriaDuplicidadEquiposController, para que las cifras coincidan con
 * esos reportes en vez de calcularse con lógica distinta.
 */
class DashboardVisualController extends Controller
{
    /** Módulos fijos que no son consultorios dinámicos. */
    private const MODULOS_FIJOS_NO_CONSULTORIO = ['infraestructura_2d', 'rrhh', 'config_modulos'];

    public function index(Request $request)
    {
        $provincias = Establecimiento::whereIn('id', function ($q) {
            $q->select('establecimiento_id')->from('mon_cabecera_monitoreo');
        })->distinct()->pluck('provincia')->filter()->sort()->values();

        $establecimientos = Establecimiento::whereIn('id', function ($q) {
            $q->select('establecimiento_id')->from('mon_cabecera_monitoreo');
        })->orderBy('nombre')->get(['id', 'nombre']);

        $fechaInicio = now()->startOfYear()->format('Y-m-d');
        $fechaFin = now()->format('Y-m-d');

        return view('usuario.dashboard.indicadores', compact(
            'provincias', 'establecimientos', 'fechaInicio', 'fechaFin'
        ));
    }

    /**
     * AJAX: estadísticas consolidadas según los filtros aplicados.
     */
    public function stats(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfYear()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', now()->format('Y-m-d'));

        $filtrarCabecera = function ($q) use ($request, $fechaInicio, $fechaFin) {
            $q->whereDate('fecha', '>=', $fechaInicio)->whereDate('fecha', '<=', $fechaFin);
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
        };

        $modulos = MonitoreoModulos::with(['cabecera.establecimiento', 'cabecera.equipos'])
            ->whereHas('cabecera', $filtrarCabecera)
            ->get();

        $totalConsultorios = 0;
        $fisico = 0;
        $funcional = 0;
        $porDepartamento = [];
        $alertas = [
            'SIN ELECTRICIDAD' => 0,
            'SIN CONECTIVIDAD' => 0,
            'SIN EQUIPOS DE CÓMPUTO' => 0,
            'REQUIERE MÁS PUNTOS DE RED' => 0,
        ];

        $conectividadPorTipo = [];
        $conectividadPorFuente = [];
        $conectividadPorProveedor = [];

        $totalTrabajadores = 0;
        $conDnie = 0;
        $serums = 0;
        $sinColegiatura = 0;
        $porServicio = [];

        $equipoSinConexion = 0;
        $conexionSinEquipo = 0;

        foreach ($modulos as $modulo) {
            $cabecera = $modulo->cabecera;
            if (!$cabecera) {
                continue;
            }

            $contenido = is_array($modulo->contenido) ? $modulo->contenido : [];

            // --- Consultorios + conectividad (todo lo que no sea un módulo fijo no-consultorio) ---
            if (!in_array($modulo->modulo_nombre, self::MODULOS_FIJOS_NO_CONSULTORIO, true)) {
                $totalConsultorios++;

                $datosConsultorio = ModuloHelper::getDatosConsultorio($cabecera, $modulo->modulo_nombre);
                $contenidoEfectivo = ModuloHelper::getContenidoEfectivo($cabecera, $modulo->modulo_nombre);
                $conectividad = ModuloHelper::getConectividadActa($cabecera, $modulo->modulo_nombre);
                $slugEquipos = ModuloHelper::getSlugEquiposEfectivo($contenido, $modulo->modulo_nombre);
                $slugEquiposUpper = strtoupper($slugEquipos);

                $cantidadEquipos = $cabecera->equipos
                    ? $cabecera->equipos->filter(fn($e) => strtoupper($e->modulo) === $slugEquiposUpper)->sum('cantidad')
                    : 0;

                if ($datosConsultorio['tipo_consultorio'] === 'FUNCIONAL') {
                    $funcional++;
                } else {
                    $fisico++;
                }

                $depto = $datosConsultorio['departamento_asociado'] ?: 'SIN DEPARTAMENTO';
                $porDepartamento[$depto] = ($porDepartamento[$depto] ?? 0) + 1;

                if (strtoupper($contenidoEfectivo['cuenta_electricidad'] ?? 'SI') === 'NO') {
                    $alertas['SIN ELECTRICIDAD']++;
                }
                if (strtoupper($conectividad['tipo'] ?? '') === 'SIN CONECTIVIDAD') {
                    $alertas['SIN CONECTIVIDAD']++;
                }
                if ($cantidadEquipos == 0) {
                    $alertas['SIN EQUIPOS DE CÓMPUTO']++;
                }
                if (strtoupper($contenidoEfectivo['requiere_mas_puntos_red'] ?? 'NO') === 'SI') {
                    $alertas['REQUIERE MÁS PUNTOS DE RED']++;
                }

                $tipoConn = $conectividad['tipo'] ?: 'SIN DATOS';
                $conectividadPorTipo[$tipoConn] = ($conectividadPorTipo[$tipoConn] ?? 0) + 1;
                if (!empty($conectividad['fuente']) && $conectividad['fuente'] !== 'N/A') {
                    $conectividadPorFuente[$conectividad['fuente']] = ($conectividadPorFuente[$conectividad['fuente']] ?? 0) + 1;
                }
                if (!empty($conectividad['operador']) && $conectividad['operador'] !== 'N/A') {
                    $conectividadPorProveedor[$conectividad['operador']] = ($conectividadPorProveedor[$conectividad['operador']] ?? 0) + 1;
                }
            }

            // --- RR.HH: trabajadores dentro del módulo fijo 'rrhh' ---
            if ($modulo->modulo_nombre === 'rrhh') {
                $trabajadores = is_array($contenido['trabajadores'] ?? null) ? $contenido['trabajadores'] : [];
                foreach ($trabajadores as $t) {
                    if (empty($t['nombres']) && empty($t['apellido_paterno']) && empty($t['doc'])) {
                        continue;
                    }
                    $totalTrabajadores++;
                    if (strtoupper($t['tiene_dnie'] ?? '') === 'SI') {
                        $conDnie++;
                    }
                    if (strtoupper($t['es_serums'] ?? '') === 'SI') {
                        $serums++;
                    }
                    if (empty($t['colegiatura'])) {
                        $sinColegiatura++;
                    }
                    $servicio = strtoupper(trim($t['servicio'] ?? '')) ?: 'OTROS';
                    $porServicio[$servicio] = ($porServicio[$servicio] ?? 0) + 1;
                }
            }

            // --- Auditoría de calidad de datos: mismo criterio que AuditoriaEquiposController,
            // aplicado a TODOS los módulos (incluidos los fijos), sobre el slug propio del módulo. ---
            $conectividadRaw = $contenido['tipo_conectividad'] ?? data_get($contenido, 'conectividad.tipo');
            $tieneDataConectividad = !empty($conectividadRaw) && mb_strtoupper($conectividadRaw, 'UTF-8') !== 'N/A';
            $tieneConexionActiva = $tieneDataConectividad && mb_strtoupper($conectividadRaw, 'UTF-8') !== 'SIN CONECTIVIDAD';
            $moduloUpper = strtoupper($modulo->modulo_nombre);
            $equiposDelModulo = $cabecera->equipos
                ? $cabecera->equipos->filter(fn($e) => strtoupper($e->modulo) === $moduloUpper)->count()
                : 0;

            if ($equiposDelModulo > 0 && !$tieneDataConectividad) {
                $equipoSinConexion++;
            } elseif ($equiposDelModulo == 0 && $tieneConexionActiva) {
                $conexionSinEquipo++;
            }
        }

        // Duplicidad de equipos: mismo criterio que AuditoriaDuplicidadEquiposController
        // (agrupado por cabecera+módulo+descripción, SUM(cantidad) > 1), sobre las mismas
        // cabeceras ya filtradas arriba.
        $cabeceraIds = $modulos->pluck('cabecera_monitoreo_id')->unique();
        $duplicados = $cabeceraIds->isEmpty() ? 0 : DB::table('mon_equipos_computo')
            ->select('cabecera_monitoreo_id', 'modulo', 'descripcion')
            ->whereIn('cabecera_monitoreo_id', $cabeceraIds)
            ->groupBy('cabecera_monitoreo_id', 'modulo', 'descripcion')
            ->havingRaw('SUM(cantidad) > 1')
            ->get()
            ->count();

        return response()->json([
            'periodoTexto' => \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($fechaFin)->format('d/m/Y'),
            'consultorios' => [
                'total' => $totalConsultorios,
                'fisico' => $fisico,
                'funcional' => $funcional,
                'porDepartamento' => $porDepartamento,
                'alertas' => $alertas,
            ],
            'conectividad' => [
                'porTipo' => $conectividadPorTipo,
                'porFuente' => $conectividadPorFuente,
                'porProveedor' => $conectividadPorProveedor,
            ],
            'rrhh' => [
                'total' => $totalTrabajadores,
                'conDnie' => $conDnie,
                'serums' => $serums,
                'sinColegiatura' => $sinColegiatura,
                'porServicio' => $porServicio,
            ],
            'auditoria' => [
                'equipoSinConexion' => $equipoSinConexion,
                'conexionSinEquipo' => $conexionSinEquipo,
                'duplicados' => $duplicados,
            ],
        ]);
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
