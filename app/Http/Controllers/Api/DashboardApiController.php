<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Establecimiento;
use App\Models\CabeceraMonitoreo;
use Illuminate\Http\JsonResponse;

class DashboardApiController extends Controller
{
    /**
     * Devuelve las estadísticas reales del Dashboard:
     * total_ipress, sin_diagnostico, con_diagnostico
     */
    public function stats(): JsonResponse
    {
        try {
            $totalIpress = Establecimiento::count();
            $conDiagnostico = CabeceraMonitoreo::distinct('establecimiento_id')
                ->whereNotNull('establecimiento_id')
                ->count('establecimiento_id');
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
     * Marcadores del mapa: establecimientos con latitud/longitud reales.
     */
    public function mapMarkers(): JsonResponse
    {
        try {
            $markers = Establecimiento::select(
                    'id', 'codigo', 'nombre', 'departamento', 'provincia',
                    'distrito', 'categoria', 'red', 'microred', 'latitud', 'longitud'
                )
                ->whereNotNull('latitud')
                ->whereNotNull('longitud')
                ->get();

            return response()->json([
                'success' => true,
                'total'   => $markers->count(),
                'markers' => $markers,
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
