<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SpeedtestController extends Controller
{
    /**
     * Endpoint de Latencia (Ping).
     */
    public function ping(Request $request)
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => microtime(true),
            'ip' => $request->ip(),
            'server_time' => now()->toIso8601String()
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * Endpoint para prueba de Descarga (Download Mbps).
     * Retorna una ráfaga binaria del tamaño solicitado (ej. 1MB - 5MB).
     */
    public function download(Request $request)
    {
        // Tamaño por defecto 2MB (2,097,152 bytes), máximo 5MB
        $sizeInMB = min(max((float)$request->query('size', 2), 0.5), 5);
        $totalBytes = (int)($sizeInMB * 1024 * 1024);

        // Generar bloque de datos binarios repetitivos de 64KB para rendimiento ultra-rápido
        $chunkSize = 65536;
        $chunk = str_repeat('X', $chunkSize);
        $fullChunks = floor($totalBytes / $chunkSize);
        $remainder = $totalBytes % $chunkSize;

        return response()->stream(function () use ($chunk, $fullChunks, $remainder) {
            for ($i = 0; $i < $fullChunks; $i++) {
                echo $chunk;
                if ($i % 10 === 0) {
                    flush();
                }
            }
            if ($remainder > 0) {
                echo substr($chunk, 0, $remainder);
            }
            flush();
        }, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Length' => $totalBytes,
            'Content-Disposition' => 'inline; filename="speedtest.bin"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache'
        ]);
    }

    /**
     * Endpoint para prueba de Subida (Upload Mbps).
     * Recibe ráfaga de datos por POST y confirma tamaño recibido.
     */
    public function upload(Request $request)
    {
        $startTime = microtime(true);
        $content = $request->getContent();
        $bytesReceived = strlen($content);
        $duration = microtime(true) - $startTime;

        return response()->json([
            'status' => 'ok',
            'bytes_received' => $bytesReceived,
            'server_duration_sec' => $duration
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
