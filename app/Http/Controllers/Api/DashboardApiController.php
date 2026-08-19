<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Establecimiento;
use App\Models\CabeceraMonitoreo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    /**
     * Mismo cálculo que UsuarioController::getProgressionContext(): un
     * establecimiento está "con diagnóstico" si tiene al menos un acta no
     * anulada, acumulado hasta el año pedido (o todas si $anioFiltro='todos').
     * Se replica aquí (en vez de reutilizar el método privado del otro
     * controlador) porque son namespaces/controladores distintos y es una
     * única consulta corta — no amerita una clase compartida todavía.
     */
    private function idsConMonitoreo(string $anioFiltro = 'todos'): array
    {
        $query = CabeceraMonitoreo::where(function ($q) {
            $q->where('anulado', 0)->orWhereNull('anulado');
        });
        if ($anioFiltro !== 'todos') {
            $query->whereYear('fecha', '<=', $anioFiltro);
        }

        return $query->distinct()->pluck('establecimiento_id')->toArray();
    }

    /**
     * Devuelve las estadísticas reales del Dashboard (solo establecimientos
     * georreferenciados, igual que la tarjeta "Total IPRESS" de
     * mapa_progresion.blade.php) y respeta el mismo filtro de año.
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $anioFiltro = $request->input('anio', 'todos');
            $idsConMonitoreo = $this->idsConMonitoreo($anioFiltro);

            $totalIpress = Establecimiento::whereNotNull('latitud')->whereNotNull('longitud')->count();
            $conDiagnostico = Establecimiento::whereNotNull('latitud')->whereNotNull('longitud')
                ->whereIn('id', $idsConMonitoreo)->count();
            $sinDiagnostico = $totalIpress - $conDiagnostico;

            return response()->json([
                'success'         => true,
                'total_ipress'    => $totalIpress,
                'sin_diagnostico' => $sinDiagnostico,
                'con_diagnostico' => $conDiagnostico,
                'timestamp'       => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Marcadores del mapa: establecimientos con latitud/longitud reales, con
     * la misma etapa (0=sin diagnóstico, 1=con diagnóstico) que colorea los
     * puntos en mapa_progresion.blade.php, y los años disponibles para el
     * filtro "Año" (acumulativo: <= año elegido).
     */
    public function mapMarkers(Request $request): JsonResponse
    {
        try {
            $anioFiltro = $request->input('anio', 'todos');
            $idsConMonitoreo = $this->idsConMonitoreo($anioFiltro);

            $totalPorId = CabeceraMonitoreo::where(function ($q) {
                $q->where('anulado', 0)->orWhereNull('anulado');
            })
                ->when($anioFiltro !== 'todos', fn ($q) => $q->whereYear('fecha', '<=', $anioFiltro))
                ->select('establecimiento_id', DB::raw('count(*) as total'))
                ->groupBy('establecimiento_id')
                ->pluck('total', 'establecimiento_id');

            $markers = Establecimiento::select(
                    'id', 'codigo', 'nombre', 'departamento', 'provincia',
                    'distrito', 'categoria', 'red', 'microred', 'latitud', 'longitud'
                )
                ->whereNotNull('latitud')
                ->whereNotNull('longitud')
                ->get()
                ->map(function ($e) use ($idsConMonitoreo, $totalPorId) {
                    $tiene = in_array($e->id, $idsConMonitoreo, true);
                    return [
                        'id' => $e->id,
                        'codigo' => $e->codigo,
                        'nombre' => $e->nombre,
                        'departamento' => $e->departamento,
                        'provincia' => $e->provincia,
                        'distrito' => $e->distrito,
                        'categoria' => $e->categoria,
                        'red' => $e->red,
                        'microred' => $e->microred,
                        'latitud' => $e->latitud,
                        'longitud' => $e->longitud,
                        'etapa' => $tiene ? 1 : 0,
                        'tiene_monitoreo' => $tiene,
                        'total_monitoreos' => (int) ($totalPorId[$e->id] ?? 0),
                    ];
                });

            $aniosDisponibles = CabeceraMonitoreo::whereNotNull('fecha')
                ->selectRaw('YEAR(fecha) as anio')
                ->distinct()
                ->pluck('anio')
                ->filter()
                ->sortDesc()
                ->values();

            return response()->json([
                'success' => true,
                'total'   => $markers->count(),
                'markers' => $markers,
                'anios_disponibles' => $aniosDisponibles,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Lista de usuarios del sistema para la app Flutter.
     */
    public function users(): JsonResponse
    {
        try {
            $users = \App\Models\User::select(
                    'id', 'apellido_paterno', 'apellido_materno', 'name',
                    'username', 'email', 'role', 'status', 'created_at'
                )
                ->orderBy('apellido_paterno')
                ->get()
                ->map(fn($u) => [
                    'id'               => $u->id,
                    'nombre_completo'  => trim("{$u->apellido_paterno} {$u->apellido_materno}, {$u->name}"),
                    'nombres'          => $u->name,
                    'apellido_paterno' => $u->apellido_paterno,
                    'apellido_materno' => $u->apellido_materno,
                    'username'         => $u->username,
                    'email'            => $u->email,
                    'role'             => $u->role,
                    'status'           => $u->status,
                    'created_at'       => optional($u->created_at)->toDateString(),
                ]);

            return response()->json(['success' => true, 'total' => $users->count(), 'users' => $users]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
