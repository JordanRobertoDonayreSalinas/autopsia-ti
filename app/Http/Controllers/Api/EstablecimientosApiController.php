<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Establecimiento;
use App\Services\RenipressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Espejo API (Sanctum) de EstablecimientoController::update/consultarRenipress
 * — mismas reglas de validación y mismo servicio RENIPRESS que la web. Solo
 * accesible a admin/operador, igual que la ruta web protegida por
 * is_operador_or_admin (ver routes/web.php).
 */
class EstablecimientosApiController extends Controller
{
    private function ensureOperadorOrAdmin(Request $request): ?JsonResponse
    {
        $role = $request->user()?->role;
        if (!in_array($role, ['admin', 'operador'], true)) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado: se requiere rol de administrador u operador.'], 403);
        }
        return null;
    }

    public function update(Request $request, $id): JsonResponse
    {
        if ($deny = $this->ensureOperadorOrAdmin($request)) return $deny;

        $establecimiento = Establecimiento::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:50|unique:establecimientos,codigo,' . $id,
            'institucion' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'departamento' => 'nullable|string|max:100',
            'provincia' => 'nullable|string|max:100',
            'distrito' => 'nullable|string|max:100',
            'centro_poblado' => 'nullable|string|max:150',
            'telefono' => 'nullable|string|max:100',
            'correo' => 'nullable|string|max:150',
            'red' => 'nullable|string|max:100',
            'microred' => 'nullable|string|max:100',
            'clas' => 'nullable|string|max:100',
            'odsis' => 'nullable|string|max:100',
            'responsable' => 'nullable|string|max:255',
            'tipo_documento' => 'nullable|string|max:50',
            'numero_documento' => 'nullable|string|max:50',
            'colegio_profesional' => 'nullable|string|max:100',
            'colegiatura' => 'nullable|string|max:50',
            'rne' => 'nullable|string|max:50',
            'categoria' => 'nullable|string|max:50',
            'estado' => 'nullable|string|max:50',
            'condicion' => 'nullable|string|max:50',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'altitud' => 'nullable|string|max:50',
            'fecha_creacion_resolucion' => 'nullable|string|max:100',
            'fecha_registro' => 'nullable|string|max:100',
            'numero_resolucion_creacion' => 'nullable|string|max:150',
            'horario_atencion' => 'nullable|string',
            'numero_ambientes' => 'nullable|string|max:50',
            'numero_camas' => 'nullable|string|max:50',
            'upss' => 'nullable|array',
            'ups' => 'nullable|array',
        ]);

        $establecimiento->update($validated);

        return response()->json(['success' => true, 'message' => 'Establecimiento actualizado con éxito.', 'establecimiento' => $establecimiento->fresh()]);
    }

    public function consultarRenipress(Request $request, $id, RenipressService $renipressService): JsonResponse
    {
        if ($deny = $this->ensureOperadorOrAdmin($request)) return $deny;

        try {
            $establecimiento = Establecimiento::findOrFail($id);
            $data = $renipressService->getDatosLimpios($establecimiento->codigo);

            if (!$data) {
                return response()->json(['ok' => false, 'mensaje' => 'El servicio de SUSALUD se encuentra temporalmente inactivo o el código es inválido.'], 404);
            }

            return response()->json(['ok' => true, 'datos' => $data]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'mensaje' => 'Error al consultar RENIPRESS: ' . $e->getMessage()], 500);
        }
    }
}
