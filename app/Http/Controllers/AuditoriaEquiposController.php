<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MonitoreoModulos;
use App\Models\Establecimiento;
use App\Models\EquipoComputo;
use App\Helpers\ModuloHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exports\AuditoriaEquiposExport;
use Maatwebsite\Excel\Facades\Excel;

class AuditoriaEquiposController extends Controller
{
    public function index(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
        $fecha_fin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));
        $provincia = $request->input('provincia');
        $distrito = $request->input('distrito');
        $establecimiento_id = $request->input('establecimiento_id');
        $implementador = $request->input('implementador');

        // Query base: Obtener todos los módulos con su cabecera y establecimiento
        $query = MonitoreoModulos::with(['cabecera.establecimiento'])
            ->whereHas('cabecera', function ($q) use ($fecha_inicio, $fecha_fin) {
                $q->whereBetween('fecha', [$fecha_inicio, $fecha_fin]);
            });

        if ($provincia) {
            $query->whereHas('cabecera.establecimiento', function ($q) use ($provincia) {
                $q->where('provincia', $provincia);
            });
        }

        if ($distrito) {
            $query->whereHas('cabecera.establecimiento', function ($q) use ($distrito) {
                $q->where('distrito', $distrito);
            });
        }

        if ($establecimiento_id) {
            $query->whereHas('cabecera', function ($q) use ($establecimiento_id) {
                $q->where('establecimiento_id', $establecimiento_id);
            });
        }

        if ($implementador) {
            $query->whereHas('cabecera', function ($q) use ($implementador) {
                $q->where('implementador', $implementador);
            });
        }

        $registros = $query->get();
        $inconsistencias = [];

        foreach ($registros as $reg) {
            $cabecera = $reg->cabecera;
            if (!$cabecera)
                continue;

            $contenido = is_string($reg->contenido) ? json_decode($reg->contenido, true) : $reg->contenido;

            // 1. Obtener conectividad (usando el helper o directamente del JSON)
            $conectividad = $contenido['tipo_conectividad'] ?? null;
            if (!$conectividad) {
                // Algunos módulos guardan conectividad dentro de una sub-clave
                $conectividad = data_get($contenido, 'conectividad.tipo');
            }

            $tiene_data_conectividad = !empty($conectividad) && mb_strtoupper($conectividad, 'UTF-8') !== 'N/A';
            $tiene_conexion_activa = ($tiene_data_conectividad && mb_strtoupper($conectividad, 'UTF-8') !== 'SIN CONECTIVIDAD');

            // 2. Contar equipos en la tabla SQL para ese monitoreo y módulo
            $equipos_count = EquipoComputo::where('cabecera_monitoreo_id', $reg->cabecera_monitoreo_id)
                ->where('modulo', $reg->modulo_nombre)
                ->count();

            $tipo_inconsistencia = null;

            // REGLAS DE NEGOCIO ACTUALIZADAS:
            // 1. Si tiene equipos, DEBE haberse registrado al menos el tipo de conectividad (Incluso si es "SIN CONECTIVIDAD").
            if ($equipos_count > 0 && !$tiene_data_conectividad) {
                $tipo_inconsistencia = 'EQUIPO SIN DATOS DE CONEXIÓN';
            }
            // 2. Si no tiene equipos y han agregado datos de conexión activa (WIFI/CABLEADO), es algo inconsistente.
            elseif ($equipos_count == 0 && $tiene_conexion_activa) {
                $tipo_inconsistencia = 'CONEXIÓN SIN EQUIPO';
            }

            if ($tipo_inconsistencia) {
                $inconsistencias[] = [
                    'acta_id' => $reg->cabecera_monitoreo_id,
                    'numero_acta' => $cabecera->numero_acta,
                    'fecha' => $cabecera->fecha,
                    'ipress' => $cabecera->establecimiento->nombre ?? 'N/A',
                    'provincia' => $cabecera->establecimiento->provincia ?? 'N/A',
                    'distrito' => $cabecera->establecimiento->distrito ?? 'N/A',
                    'modulo_nombre' => ModuloHelper::getNombreAmigable($reg->modulo_nombre),
                    'equipos_count' => $equipos_count,
                    'conectividad' => $conectividad ?: 'SIN DATOS',
                    'implementador' => $cabecera->implementador ?? 'N/A',
                    'tipo_inconsistencia' => $tipo_inconsistencia
                ];
            }
        }

        if ($request->has('export')) {
            return Excel::download(new AuditoriaEquiposExport($inconsistencias), 'auditoria_equipos_' . date('Ymd_His') . '.xlsx');
        }

        return view('usuario.auditoria.equipos', [
            'inconsistencias' => $inconsistencias,
            'provincias' => Establecimiento::whereHas('monitoreos.detalles')
                ->distinct()
                ->pluck('provincia')
                ->filter()
                ->sort(),
            'distritos' => $provincia ? Establecimiento::where('provincia', $provincia)
                ->whereHas('monitoreos.detalles')
                ->distinct()
                ->pluck('distrito')
                ->filter()
                ->sort() : [],
            'establecimientos' => Establecimiento::whereHas('monitoreos.detalles')
                ->when($provincia, fn($q) => $q->where('provincia', $provincia))
                ->when($distrito, fn($q) => $q->where('distrito', $distrito))
                ->orderBy('nombre')
                ->get(),
            'implementadores' => \App\Models\CabeceraMonitoreo::distinct()
                ->whereNotNull('implementador')
                ->pluck('implementador')
                ->sort(),
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin
        ]);
    }

    public function ajaxGetDistritos(Request $request)
    {
        $distritos = Establecimiento::whereHas('monitoreos.detalles')
            ->when($request->provincia, fn($q) => $q->where('provincia', $request->provincia))
            ->distinct()
            ->pluck('distrito')
            ->filter()
            ->sort()
            ->values();

        return response()->json($distritos);
    }

    public function ajaxGetEstablecimientos(Request $request)
    {
        $establecimientos = Establecimiento::whereHas('monitoreos.detalles')
            ->when($request->provincia, fn($q) => $q->where('provincia', $request->provincia))
            ->when($request->distrito, fn($q) => $q->where('distrito', $request->distrito))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($establecimientos);
    }
}
