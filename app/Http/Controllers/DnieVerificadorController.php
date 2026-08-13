<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DnieVerificadorController extends Controller
{
    /**
     * Verifica si un DNI tiene DNI Electrónico (DNIe) consultando el portal PKI de RENIEC.
     *
     * Endpoint oficial (no requiere reCAPTCHA real):
     *   POST https://pki.reniec.gob.pe/ciudadanodigital/consulta-certificados/obtener-vigencia
     *   Body: { "numeroDni": "12345678", "recaptchaToken": "" }
     *
     * Respuesta conocida:
     *   {
     *     "estado": "ok",
     *     "datos": {
     *       "resultado":           "OK",
     *       "tieneDNIe":           "SI" | "NO",
     *       "certificadoVigente":  "SI" | "NO",
     *       "fechaExpiracion":     "DD/MM/YYYY HH:MM:SS" | "",
     *       // Posibles campos adicionales (versión, modelo, etc.) — se pasan todos
     *     },
     *     "mensaje": null
     *   }
     *
     * Caché: 60 minutos por DNI para no saturar el servicio de RENIEC.
     *
     * @param  string  $dni  8 dígitos del DNI a consultar
     */
    public function verificar(Request $request, string $dni)
    {
        if (!preg_match('/^\d{8}$/', $dni)) {
            return response()->json([
                'success' => false,
                'error'   => 'El DNI debe tener exactamente 8 dígitos numéricos.',
            ], 422);
        }

        $cacheKey = 'dnie_pki_' . $dni;

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $payload = json_encode([
            'numeroDni'      => $dni,
            'recaptchaToken' => '',
        ]);

        $ch = curl_init('https://pki.reniec.gob.pe/ciudadanodigital/consulta-certificados/obtener-vigencia');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json, text/plain, */*',
                'Origin: https://pki.reniec.gob.pe',
                'Referer: https://pki.reniec.gob.pe/ciudadanodigital/consulta-certificados',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Edg/124.0.0.0',
            ],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            Log::error("DnieVerificador cURL error DNI {$dni}: {$curlError}");
            return response()->json([
                'success' => false,
                'error'   => 'No se pudo conectar con el servicio de RENIEC.',
            ], 503);
        }

        $data = json_decode($responseBody, true);

        if ($httpCode !== 200 || !is_array($data) || ($data['estado'] ?? '') !== 'ok') {
            Log::warning("DnieVerificador respuesta inesperada DNI {$dni}. HTTP {$httpCode}: {$responseBody}");
            return response()->json([
                'success' => false,
                'error'   => 'Respuesta inesperada del servicio de RENIEC.',
            ], 502);
        }

        $datos = $data['datos'] ?? [];

        // Loguear TODOS los campos que devuelve RENIEC para detectar campos nuevos (versión, etc.)
        Log::info("DnieVerificador DNI {$dni} — campos en 'datos': " . json_encode($datos));

        // ── Campos conocidos ──────────────────────────────────────────────────────
        $tieneDNIe          = ($datos['tieneDNIe']          ?? 'NO') === 'SI';
        $certificadoVigente = ($datos['certificadoVigente'] ?? 'NO') === 'SI';
        $fechaExpiracion    = $datos['fechaExpiracion']    ?? '';

        // ── Versión del DNIe ──────────────────────────────────────────────────────
        // RENIEC no expone un campo explícito de versión en este endpoint.
        // Se intenta detectar desde campos alternativos que puedan aparecer.
        // Si el API alguna vez devuelve un campo tipo "version", "modelo", "tipoDnie"
        // o "versionChip", se captura aquí automáticamente.
        $versionRaw = $datos['version']    ?? $datos['modelo']    ??
                      $datos['tipoDnie']   ?? $datos['versionChip'] ??
                      $datos['versionDni'] ?? null;

        // Si RENIEC no lo devuelve, intentamos inferirlo por rango de fechaExpiracion:
        // DNIe 1.0 → emisiones antes de 2015 (vencen ~2019)
        // DNIe 2.0 → emisiones 2015-2021 (vencen ~2025)
        // DNIe 3.0 → emisiones desde 2022 (vencen ~2026+)
        $versionInferida = null;
        if (!$versionRaw && $tieneDNIe && $fechaExpiracion) {
            try {
                // fechaExpiracion viene como "DD/MM/YYYY HH:MM:SS"
                $parts     = explode(' ', $fechaExpiracion);
                $dateParts = explode('/', $parts[0]);
                if (count($dateParts) === 3) {
                    $anioExpiracion = (int) $dateParts[2];
                    if ($anioExpiracion <= 2019)      $versionInferida = '1.0';
                    elseif ($anioExpiracion <= 2025)  $versionInferida = '2.0';
                    else                              $versionInferida = '3.0';
                }
            } catch (\Throwable $e) {
                // No se pudo inferir — se deja null
            }
        }

        $result = [
            'success'            => true,
            'dni'                => $dni,
            'tieneDNIe'          => $tieneDNIe,
            'certificadoVigente' => $certificadoVigente,
            'fechaExpiracion'    => $fechaExpiracion,
            // Versión: primero la que devuelva RENIEC, luego la inferida por fecha
            'versionDnie'        => $versionRaw ?? $versionInferida,
            'versionFuente'      => $versionRaw ? 'reniec' : ($versionInferida ? 'inferida' : null),
            // Todos los campos extra que devuelva RENIEC (para detección futura)
            'datosExtra'         => collect($datos)->except([
                'resultado', 'tieneDNIe', 'certificadoVigente', 'fechaExpiracion',
            ])->toArray(),
        ];

        Cache::put($cacheKey, $result, now()->addMinutes(60));

        return response()->json($result);
    }
}
