<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RenipressService;

class RenipressController extends Controller
{
    protected $renipressService;

    public function __construct(RenipressService $renipressService)
    {
        $this->renipressService = $renipressService;
    }

    /**
     * Endpoint HTTP para el Agente (Antigravity).
     * Recibe 'idipress' vía POST y valida la cabecera 'X-API-KEY'.
     */
    public function consultar(Request $request)
    {
        // 1. Validar autenticación con X-API-KEY
        $apiKeyHeader = $request->header('X-API-KEY');
        $expectedKey = env('RENIPRESS_API_KEY');

        if (!empty($expectedKey) && $apiKeyHeader !== $expectedKey) {
            return response()->json([
                'error' => 'No autorizado. La clave X-API-KEY es incorrecta.'
            ], 401);
        }

        // 2. Obtener y validar el idipress del body
        $idipress = $request->input('idipress');
        if (empty($idipress)) {
            return response()->json([
                'error' => 'El código idipress es requerido.'
            ], 400);
        }

        // 3. Consultar los datos limpios de RENIPRESS
        $data = $this->renipressService->getDatosLimpios($idipress);

        if (!$data) {
            return response()->json([
                'error' => 'El servicio de SUSALUD se encuentra temporalmente inactivo o el código es inválido'
            ], 500);
        }

        return response()->json($data, 200);
    }
}
