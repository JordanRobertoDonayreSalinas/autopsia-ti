<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DnieReporteExport;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReporteDnieController extends Controller
{
    /**
     * Enviar respuesta de error ya sea en AJAX o recarga normal.
     */
    private function sendError(Request $request, string $mensaje)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['errors' => ['archivo' => [$mensaje]]], 422);
        }
        return back()->withErrors(['archivo' => $mensaje]);
    }

    /**
     * Muestra la vista principal del reporte de DNI Electrónico.
     */
    public function index()
    {
        return view('usuario.reportes.dnie');
    }

    /**
     * Procesa el Excel subido, consulta RENIEC por cada DNI
     * y devuelve un nuevo Excel enriquecido con datos del DNIe.
     */
    public function procesar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'archivo.required'  => 'Debe seleccionar un archivo Excel.',
            'archivo.mimes'     => 'El archivo debe ser .xlsx, .xls o .csv.',
            'archivo.max'       => 'El archivo no debe superar los 10 MB.',
        ]);

        $extension = $request->file('archivo')->getClientOriginalExtension();
        if ($extension !== 'xlsx' && $extension !== 'xls') {
            return $this->sendError($request, 'El archivo debe ser un Excel (.xlsx, .xls).');
        }

        // Ampliar tiempo de ejecución: 62 filas x ~1-8s/petición puede superar los 60s por defecto
        set_time_limit(300);
        ini_set('max_execution_time', 300);

        try {
            // ── Leer el archivo Excel ────────────────────────────────────────
            $file        = $request->file('archivo');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, true); // asociativo por letra

            if (empty($rows) || count($rows) < 2) {
                return $this->sendError($request, 'El archivo no contiene datos suficientes (mínimo 1 fila de encabezado + 1 fila de datos).');
            }

            // Detectar columnas automáticamente desde la primera fila (encabezados)
            $headerRow   = array_shift($rows); // extrae la fila 1
            $colMap      = $this->detectColumns($headerRow);

            if (!$colMap) {
                return $this->sendError($request, 'No se pudo identificar la columna de "Número de Documento" en el archivo. Verifique los encabezados.');
            }

            // ── Procesar cada fila ───────────────────────────────────────────
            $resultados = [];
            $total      = count($rows);

            if ($total > 500) {
                return back()->withErrors(['archivo' => "El archivo contiene {$total} filas. El límite es 500 por procesamiento para no sobrecargar el servicio de RENIEC."]);
            }

            foreach ($rows as $row) {
                // Saltar filas completamente vacías
                $allEmpty = collect($row)->filter(fn($v) => !is_null($v) && trim((string)$v) !== '')->isEmpty();
                if ($allEmpty) continue;

                $fila = $this->extraerFila($row, $colMap);

                // Solo consultar RENIEC si es DNI de 8 dígitos
                if ($this->esDocumentoDni($fila['tipo_documento'], $fila['num_documento'])) {
                    $dnieData = $this->consultarReniec($fila['num_documento']);
                    $fila     = array_merge($fila, $dnieData);
                } else {
                    $fila = array_merge($fila, [
                        'tiene_dnie'          => 'N/A',
                        'version_dnie'        => 'N/A',
                        'cert_vigente'        => 'N/A',
                        'fecha_expiracion'    => 'N/A',
                        'version_fuente'      => 'N/A',
                        'estado_consulta'     => 'NO APLICA',
                    ]);
                }

                $resultados[] = $fila;
            }

            // ── Generar y descargar Excel ────────────────────────────────────
            $filename = 'Reporte_DNIe_' . date('Ymd_His') . '.xlsx';

            return Excel::download(new DnieReporteExport(collect($resultados)), $filename);

        } catch (\Exception $e) {
            \Log::error('Error procesando reporte DNIe: ' . $e->getMessage());
            return $this->sendError($request, 'Ocurrió un error al procesar el archivo. Por favor verifica que el formato sea correcto.');
        }
    }

    // ════════════════════════════════════════════════════════════════════════
    // HELPERS PRIVADOS
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Detecta el mapa de columnas del encabezado del Excel (flexible a nombres variados).
     * Retorna un array ['col_key' => 'LetraExcel'] o null si no se puede detectar.
     */
    private function detectColumns(array $headerRow): ?array
    {
        $map = [];

        $columnasBusqueda = [
            'ipress'            => ['ipress', 'código ipress', 'codigo ipress', 'cod ipress', 'ipress/microred'],
            'nombre_estab'      => ['establecimiento', 'nombre de establecimiento', 'nombre establecimiento', 'nombre_establecimiento'],
            'categoria'         => ['cat', 'categoria', 'categoría', 'cat.'],
            'distrito'          => ['distrito'],
            'provincia'         => ['provincia'],
            'microred'          => ['microred', 'micro red'],
            'red'               => ['red'],
            'profesion'         => ['profesion', 'profesión', 'cargo', 'profesion/cargo'],
            'tipo_documento'    => ['tipo_doc', 'tipo de documento', 'tipo documento', 'tipo_documento', 'tipo doc'],
            'num_documento'     => ['doc_personal', 'numero de documento', 'número de documento', 'nro documento',
                                    'nro. documento', 'num documento', 'número documento', 'dni', 'documento'],
            'ap_paterno'        => ['apellido_paterno_personal', 'apellido paterno', 'apellido_paterno', 'ap paterno', 'ap. paterno'],
            'ap_materno'        => ['apellido_materno_personal', 'apellido materno', 'apellido_materno', 'ap materno', 'ap. materno'],
            'nombres'           => ['nombres_personal', 'nombres', 'nombre'],
        ];

        foreach ($headerRow as $letra => $valor) {
            $valorLimpio = mb_strtolower(trim((string) $valor));
            foreach ($columnasBusqueda as $key => $variantes) {
                if (!isset($map[$key]) && in_array($valorLimpio, $variantes)) {
                    $map[$key] = $letra;
                }
            }
        }

        // La columna de número de documento es obligatoria
        if (!isset($map['num_documento'])) {
            return null;
        }

        return $map;
    }

    /**
     * Extrae los datos de una fila según el mapa de columnas detectado.
     */
    private function extraerFila(array $row, array $colMap): array
    {
        $get = fn(string $key) => isset($colMap[$key]) ? trim((string)($row[$colMap[$key]] ?? '')) : '';

        // El número de documento puede llegar sin ceros iniciales si Excel lo leyó como número.
        $numDoc = $get('num_documento');
        $tipoDoc = mb_strtoupper($get('tipo_documento'));

        if (ctype_digit($numDoc)) {
            if (in_array($tipoDoc, ['CE', 'CARNET DE EXTRANJERÍA'])) {
                $numDoc = str_pad($numDoc, 9, '0', STR_PAD_LEFT);
            } elseif (!in_array($tipoDoc, ['PASAPORTE', 'RUC', 'OTRO'])) {
                $numDoc = str_pad($numDoc, 8, '0', STR_PAD_LEFT);
            }
        }

        return [
            'ipress'         => $get('ipress'),
            'nombre_estab'   => $get('nombre_estab'),
            'categoria'      => $get('categoria'),
            'distrito'       => $get('distrito'),
            'provincia'      => $get('provincia'),
            'microred'       => $get('microred'),
            'red'            => $get('red'),
            'profesion'      => $get('profesion'),
            'tipo_documento' => $get('tipo_documento'),
            'num_documento'  => $numDoc,
            'ap_paterno'     => $get('ap_paterno'),
            'ap_materno'     => $get('ap_materno'),
            'nombres'        => $get('nombres'),
        ];
    }

    /**
     * Determina si el tipo de documento y número corresponden a un DNI peruano
     * válido (8 dígitos numéricos).
     */
    private function esDocumentoDni(string $tipo, string $numero): bool
    {
        $tipoUpper = mb_strtoupper(trim($tipo));
        $esDni     = in_array($tipoUpper, ['DNI', 'D.N.I', 'D.N.I.', '']) || $tipoUpper === '';

        // Si el tipo no dice explícitamente algo diferente a DNI, lo intentamos
        // si el número tiene exactamente 8 dígitos
        return preg_match('/^\d{8}$/', $numero) && !in_array($tipoUpper, ['CE', 'CARNET DE EXTRANJERÍA', 'PASAPORTE', 'RUC', 'OTRO']);
    }

    /**
     * Consulta el portal PKI de RENIEC para un DNI dado.
     * Reutiliza el caché de 60 minutos compartido con DnieVerificadorController.
     */
    private function consultarReniec(string $dni): array
    {
        $cacheKey = 'dnie_pki_' . $dni;

        // ── Caché hit ────────────────────────────────────────────────────────
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            return $this->formatearResultado($cached);
        }

        // ── Pequeña pausa SOLO en peticiones reales (no en cache hits) ─────
        // Se aplica más abajo, justo antes de curl_exec

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
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            ],
            CURLOPT_TIMEOUT        => 8,   // RENIEC responde en <1s; 8s es suficiente margen
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        // Pausa mínima entre peticiones reales para no saturar RENIEC
        usleep(50_000); // 50 ms

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200) {
            Log::warning("ReporteDnie: error RENIEC DNI {$dni}. HTTP {$httpCode}. cURL: {$curlError}");
            return [
                'tiene_dnie'       => 'ERROR',
                'version_dnie'     => '-',
                'cert_vigente'     => 'ERROR',
                'fecha_expiracion' => '-',
                'version_fuente'   => '-',
                'estado_consulta'  => 'ERROR CONEXIÓN',
            ];
        }

        $data = json_decode($responseBody, true);

        if (!is_array($data) || ($data['estado'] ?? '') !== 'ok') {
            return [
                'tiene_dnie'       => 'ERROR',
                'version_dnie'     => '-',
                'cert_vigente'     => 'ERROR',
                'fecha_expiracion' => '-',
                'version_fuente'   => '-',
                'estado_consulta'  => 'RESPUESTA INESPERADA',
            ];
        }

        $datos = $data['datos'] ?? [];

        $tieneDNIe          = ($datos['tieneDNIe']          ?? 'NO') === 'SI';
        $certificadoVigente = ($datos['certificadoVigente'] ?? 'NO') === 'SI';
        $fechaExpiracion    = $datos['fechaExpiracion'] ?? '';

        // Versión del DNIe
        $versionRaw = $datos['version'] ?? $datos['modelo'] ?? $datos['tipoDnie'] ??
                      $datos['versionChip'] ?? $datos['versionDni'] ?? null;

        $versionInferida = null;
        if (!$versionRaw && $tieneDNIe && $fechaExpiracion) {
            try {
                $parts     = explode(' ', $fechaExpiracion);
                $dateParts = explode('/', $parts[0]);
                if (count($dateParts) === 3) {
                    $anio = (int) $dateParts[2];
                    if ($anio <= 2019)     $versionInferida = '1.0';
                    elseif ($anio <= 2025) $versionInferida = '2.0';
                    else                  $versionInferida = '3.0';
                }
            } catch (\Throwable $e) { /* silencio */ }
        }

        $result = [
            'success'            => true,
            'dni'                => $dni,
            'tieneDNIe'          => $tieneDNIe,
            'certificadoVigente' => $certificadoVigente,
            'fechaExpiracion'    => $fechaExpiracion,
            'versionDnie'        => $versionRaw ?? $versionInferida,
            'versionFuente'      => $versionRaw ? 'reniec' : ($versionInferida ? 'inferida' : null),
        ];

        Cache::put($cacheKey, $result, now()->addMinutes(60));

        return $this->formatearResultado($result);
    }

    /**
     * Convierte el resultado normalizado de RENIEC al formato de columnas del reporte.
     */
    private function formatearResultado(array $r): array
    {
        if (!($r['success'] ?? false)) {
            return [
                'tiene_dnie'       => 'ERROR',
                'version_dnie'     => '-',
                'cert_vigente'     => 'ERROR',
                'fecha_expiracion' => '-',
                'version_fuente'   => '-',
                'estado_consulta'  => 'ERROR',
            ];
        }

        $tieneDNIe = $r['tieneDNIe'] ?? false;
        $certVig   = $r['certificadoVigente'] ?? false;

        return [
            'tiene_dnie'       => $tieneDNIe ? 'SI' : 'NO',
            'version_dnie'     => $r['versionDnie'] ?? ($tieneDNIe ? 'No especificada' : '-'),
            'cert_vigente'     => $tieneDNIe ? ($certVig ? 'SI' : 'NO') : '-',
            'fecha_expiracion' => $r['fechaExpiracion'] ?? '-',
            'version_fuente'   => match($r['versionFuente'] ?? null) {
                'reniec'   => 'RENIEC',
                'inferida' => 'Inferida por fecha',
                default    => $tieneDNIe ? 'No disponible' : '-',
            },
            'estado_consulta'  => $tieneDNIe ? ($certVig ? 'DNIe ACTIVO' : 'Certificado digital vencido') : 'SIN DNIe',
        ];
    }
}
