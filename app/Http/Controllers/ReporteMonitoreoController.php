<?php

namespace App\Http\Controllers;

use App\Models\CabeceraMonitoreo;
use App\Models\Establecimiento;
use Illuminate\Http\Request;
use App\Exports\ActasMonitoreoExport;
use Maatwebsite\Excel\Facades\Excel;

class ReporteMonitoreoController extends Controller
{
    /**
     * Muestra el reporte de Actas de Monitoreo.
     */
    public function index(Request $request)
    {
        // Persistencia de fechas en sesión
        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            session(['mon_reporte_fecha_inicio' => $request->input('fecha_inicio')]);
            session(['mon_reporte_fecha_fin'    => $request->input('fecha_fin')]);
        } else {
            $request->merge([
                'fecha_inicio' => session('mon_reporte_fecha_inicio', now()->startOfYear()->format('Y-m-d')),
                'fecha_fin'    => session('mon_reporte_fecha_fin',    now()->format('Y-m-d')),
            ]);
        }

        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin    = $request->input('fecha_fin');

        // Listas para filtros
        $implementadores = CabeceraMonitoreo::distinct()->orderBy('implementador')
            ->pluck('implementador')->filter();

        $provincias = Establecimiento::whereIn('id',
            CabeceraMonitoreo::select('establecimiento_id')
        )->distinct()->orderBy('provincia')->pluck('provincia')->filter();

        // Query principal
        $query = CabeceraMonitoreo::with(['establecimiento', 'detalles', 'equipo'])
            ->whereDate('fecha', '>=', $fechaInicio)
            ->whereDate('fecha', '<=', $fechaFin);

        if ($request->filled('implementador')) {
            $query->where('implementador', $request->implementador);
        }

        if ($request->filled('tipo_origen')) {
            $query->where('tipo_origen', $request->tipo_origen);
        }

        if ($request->filled('provincia')) {
            $query->whereHas('establecimiento', fn($q) =>
                $q->where('provincia', $request->provincia));
        }

        if ($request->filled('distrito')) {
            $query->whereHas('establecimiento', fn($q) =>
                $q->where('distrito', $request->distrito));
        }

        if ($request->filled('establecimiento_id')) {
            $query->where('establecimiento_id', $request->establecimiento_id);
        }

        if ($request->filled('firmado') && $request->firmado !== '') {
            $query->where('firmado', (bool) $request->firmado);
        }

        $actas = $query->orderByDesc('fecha')->paginate(25)->withQueryString();

        // KPIs
        $baseKpi = CabeceraMonitoreo::whereDate('fecha', '>=', $fechaInicio)
            ->whereDate('fecha', '<=', $fechaFin);
        $totalFirmadas   = (clone $baseKpi)->where('firmado', true)->count();
        $totalPendientes = (clone $baseKpi)->where('firmado', false)->count();
        $totalEspecializada = (clone $baseKpi)->where('tipo_origen', 'ESPECIALIZADA')->count();
        $totalEstandar   = (clone $baseKpi)->where('tipo_origen', 'ESTANDAR')->count();

        return view('usuario.reportes.actas_monitoreo', compact(
            'actas', 'implementadores', 'provincias',
            'fechaInicio', 'fechaFin',
            'totalFirmadas', 'totalPendientes',
            'totalEspecializada', 'totalEstandar'
        ));
    }

    /**
     * Exporta el reporte a Excel con los mismos filtros.
     */
    public function exportarExcel(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfYear()->format('Y-m-d'));
        $fechaFin    = $request->input('fecha_fin',    now()->format('Y-m-d'));

        $query = CabeceraMonitoreo::with(['establecimiento', 'detalles', 'equipo'])
            ->whereDate('fecha', '>=', $fechaInicio)
            ->whereDate('fecha', '<=', $fechaFin);

        if ($request->filled('implementador')) {
            $query->where('implementador', $request->implementador);
        }
        if ($request->filled('tipo_origen')) {
            $query->where('tipo_origen', $request->tipo_origen);
        }
        if ($request->filled('provincia')) {
            $query->whereHas('establecimiento', fn($q) =>
                $q->where('provincia', $request->provincia));
        }
        if ($request->filled('distrito')) {
            $query->whereHas('establecimiento', fn($q) =>
                $q->where('distrito', $request->distrito));
        }
        if ($request->filled('establecimiento_id')) {
            $query->where('establecimiento_id', $request->establecimiento_id);
        }
        if ($request->filled('firmado') && $request->firmado !== '') {
            $query->where('firmado', (bool) $request->firmado);
        }

        $actas    = $query->orderByDesc('fecha')->get();
        $filename = 'Reporte_Actas_Monitoreo_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new ActasMonitoreoExport($actas), $filename);
    }

    /**
     * AJAX: Distritos filtrados por provincia.
     */
    public function ajaxGetDistritos(Request $request)
    {
        $query = Establecimiento::whereIn('id',
            CabeceraMonitoreo::select('establecimiento_id'));

        if ($request->filled('provincia')) {
            $query->where('provincia', $request->provincia);
        }

        return response()->json(
            $query->distinct()->orderBy('distrito')->pluck('distrito')->filter()->values()
        );
    }

    /**
     * AJAX: Establecimientos filtrados por provincia y/o distrito.
     */
    public function ajaxGetEstablecimientos(Request $request)
    {
        $query = Establecimiento::whereIn('id',
            CabeceraMonitoreo::select('establecimiento_id'));

        if ($request->filled('provincia')) {
            $query->where('provincia', $request->provincia);
        }
        if ($request->filled('distrito')) {
            $query->where('distrito', $request->distrito);
        }

        return response()->json(
            $query->orderBy('nombre')->get(['id', 'nombre'])
        );
    }
}
