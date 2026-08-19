<?php

namespace App\Http\Controllers\Api;

use App\Console\Commands\ExportSchemaJson;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class SchemaApiController extends Controller
{
    /**
     * GET /api/v1/schema
     *
     * Devuelve el contrato de datos real de las tablas "offline-first" (ver
     * Informe de revisión, Fase 0) para que Flutter derive su esquema SQLite
     * y sus modelos Dart de Laravel en vez de mantenerlos escritos a mano.
     */
    public function show(): JsonResponse
    {
        $path = storage_path('app/database_schema.json');

        // Si el archivo no existe o quedó desactualizado (más viejo que 1 día),
        // se regenera desde information_schema antes de responder.
        if (!file_exists($path) || filemtime($path) < now()->subDay()->timestamp) {
            Artisan::call(ExportSchemaJson::class);
        }

        if (!file_exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo generar el esquema.',
            ], 500);
        }

        $schema = json_decode(file_get_contents($path), true);

        return response()->json([
            'success' => true,
            'schema'  => $schema,
        ]);
    }
}
