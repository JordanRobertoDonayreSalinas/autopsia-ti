<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CabeceraMonitoreo;
use App\Models\Establecimiento;
use App\Models\EquipoComputo;
use App\Helpers\ModuloHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AuditoriaDuplicidadEquiposController extends Controller
{
    public function index(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
        $fecha_fin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));
        $provincia = $request->input('provincia');
        $distrito = $request->input('distrito');
        $establecimiento_id = $request->input('establecimiento_id');
        $implementador = $request->input('implementador');

        // Buscar duplicados: Agrupar por cabecera, modulo y descripcion, sumando cantidad
        $duplicadosRaw = DB::table('mon_equipos_computo')
            ->select('cabecera_monitoreo_id', 'modulo', 'descripcion', DB::raw('SUM(cantidad) as total_equipos'))
            ->join('mon_cabecera_monitoreo', 'mon_equipos_computo.cabecera_monitoreo_id', '=', 'mon_cabecera_monitoreo.id')
            ->join('establecimientos', 'mon_cabecera_monitoreo.establecimiento_id', '=', 'establecimientos.id')
            ->whereBetween('mon_cabecera_monitoreo.fecha', [$fecha_inicio, $fecha_fin])
            ->when($provincia, fn($q) => $q->where('establecimientos.provincia', $provincia))
            ->when($distrito, fn($q) => $q->where('establecimientos.distrito', $distrito))
            ->when($establecimiento_id, fn($q) => $q->where('mon_cabecera_monitoreo.establecimiento_id', $establecimiento_id))
            ->when($implementador, fn($q) => $q->where('mon_cabecera_monitoreo.implementador', $implementador))
            ->groupBy('cabecera_monitoreo_id', 'modulo', 'descripcion')
            ->havingRaw('SUM(cantidad) > 1')
            ->get();

        $cabeceraIds = $duplicadosRaw->pluck('cabecera_monitoreo_id')->unique()->toArray();
        $cabeceras = CabeceraMonitoreo::with('establecimiento')
            ->whereIn('id', $cabeceraIds)
            ->get()
            ->keyBy('id');

        $inconsistencias = [];

        foreach ($duplicadosRaw as $dup) {
            $cabecera = $cabeceras->get($dup->cabecera_monitoreo_id);
            if (!$cabecera) continue;

            $inconsistencias[] = [
                'acta_id' => $dup->cabecera_monitoreo_id,
                'numero_acta' => $cabecera->numero_acta,
                'fecha' => $cabecera->fecha,
                'ipress' => $cabecera->establecimiento->nombre ?? 'N/A',
                'provincia' => $cabecera->establecimiento->provincia ?? 'N/A',
                'distrito' => $cabecera->establecimiento->distrito ?? 'N/A',
                'modulo_nombre' => ModuloHelper::getNombreAmigable($dup->modulo),
                'equipo_tipo' => mb_strtoupper($dup->descripcion, 'UTF-8'),
                'cantidad' => $dup->total_equipos,
                'implementador' => $cabecera->implementador ?? 'N/A',
                'tipo_inconsistencia' => 'POSIBLE DUPLICIDAD: ' . $dup->total_equipos . ' ' . mb_strtoupper($dup->descripcion, 'UTF-8') . '(S)'
            ];
        }

        return view('usuario.auditoria.duplicidad', [
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
