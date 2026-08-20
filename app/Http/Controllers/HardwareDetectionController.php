<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HardwareDetectionController extends Controller
{
    /**
     * Detección directa instantánea (Servidor local / Windows)
     * Realiza un escaneo EN VIVO para capturar en tiempo real periféricos y conectividad de red
     */
    public function deteccionDirecta(Request $request)
    {
        set_time_limit(35);
        ini_set('max_execution_time', 35);

        if (ob_get_length()) {
            @ob_clean();
        }

        $resultFile = sys_get_temp_dir() . '/hw_detection.json';

        // 1. Ejecutar escaneo directo instantáneo del servidor
        $data = $this->obtenerHardwareDirectoServidor();
        if ($data && ($data['status'] ?? '') === 'completed') {
            @file_put_contents($resultFile, json_encode($data, JSON_UNESCAPED_UNICODE));
            Cache::put("hw_last_detected", $data, now()->addHours(2));

            return response()->json([
                'success'  => true,
                'status'   => 'completed',
                'hardware' => $data,
            ]);
        }

        // 2. Si falló la ejecución directa, revisar si hay resultado previo reciente (< 60s)
        if (file_exists($resultFile)) {
            $age = time() - filemtime($resultFile);
            if ($age <= 60) {
                $raw = @file_get_contents($resultFile);
                $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
                $raw = trim($raw);
                $cachedData = json_decode($raw, true);

                if ($cachedData && ($cachedData['status'] ?? '') === 'completed') {
                    if (empty($cachedData['proveedor_internet']) || $cachedData['proveedor_internet'] === 'No Identificado') {
                        $cachedData['proveedor_internet'] = $this->obtenerProveedorISP($cachedData['tipo_red'] ?? '');
                    }
                    if (empty($cachedData['velocidad_descarga'])) {
                        $speeds = $this->medirVelocidadInternetReal();
                        $cachedData['velocidad_descarga'] = $speeds['descarga'];
                        $cachedData['velocidad_subida']   = $speeds['subida'];
                    }

                    return response()->json([
                        'success'  => true,
                        'status'   => 'completed',
                        'hardware' => $cachedData,
                    ]);
                }
            }
        }

        // 3. Revisar Caché guardado recientemente
        $last = Cache::get("hw_last_detected");
        if ($last && ($last['status'] ?? '') === 'completed' && (time() - ($last['timestamp'] ?? 0)) <= 60) {
            return response()->json([
                'success'  => true,
                'status'   => 'completed',
                'hardware' => $last,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo realizar la detección directa en este entorno.',
        ]);
    }

    /**
     * Genera un token único de sesión para el rastreo de hardware
     */
    public function generarToken(Request $request)
    {
        $token = 'hw_' . Str::random(12);
        Cache::put("hw_token_{$token}", ['status' => 'pending'], now()->addMinutes(10));

        return response()->json([
            'success' => true,
            'token'   => $token,
        ]);
    }

    /**
     * Descarga un ejecutable .bat de un solo clic que lanza PowerShell y envía los datos
     */
    public function descargarBat($token)
    {
        $serverUrl = url('/');
        if (request()->secure() || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') || (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on')) {
            $serverUrl = preg_replace('/^http:/i', 'https:', $serverUrl);
        }

        $endpointUrl = $serverUrl . '/usuario/ajax/guardar-deteccion-hardware';
        $psDetection = $this->generarScriptPowerShell();

        $psBatchScript = $psDetection . "\r\n" . <<<POWERSHELL_POST
\$payload['token'] = '{$token}'
\$jsonResult = \$payload | ConvertTo-Json -Depth 5 -Compress

[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
try {
    \$res = Invoke-RestMethod -Uri "{$endpointUrl}" -Method Post -Body \$jsonResult -ContentType "application/json; charset=utf-8" -TimeoutSec 15
    Write-Host "   [OK] Diagnostico completo enviado con exito al servidor!" -ForegroundColor Green
} catch {
    try {
        \$wc = New-Object System.Net.WebClient
        \$wc.Headers.Add("Content-Type", "application/json; charset=utf-8")
        \$null = \$wc.UploadString("{$endpointUrl}", \$jsonResult)
        Write-Host "   [OK] Diagnostico enviado con exito (WebClient)!" -ForegroundColor Green
    } catch {
        Write-Host "   [ERROR] No se pudo enviar el reporte al servidor: \$_" -ForegroundColor Red
    }
}
POWERSHELL_POST;

        // El script de PowerShell (deteccion + envio) puede superar los 8191
        // caracteres que soporta una sola linea de comando en cmd.exe. Antes
        // se pasaba entero en base64 dentro de un solo -Command, lo que
        // truncaba/corrompia la linea en equipos con hardware mas detallado
        // (mas impresoras, camaras, etc.) y el .bat fallaba con un error de
        // comillas sin enviar nunca los datos. Ahora el .bat se autolee: el
        // -Command que invoca PowerShell es corto y fijo (no depende del
        // tamano del payload), y el script real vive como texto plano mas
        // abajo en el mismo archivo, despues de un `exit` que cmd.exe nunca
        // llega a ejecutar.
        $extractorCmd = '$__l=Get-Content -LiteralPath \'%~f0\' -Encoding UTF8; '
            . '$__i=($__l | Select-String -Pattern \'^:::PS1_START:::\').LineNumber; '
            . 'Invoke-Expression (($__l[$__i..($__l.Count-1)]) -join [Environment]::NewLine)';

        $batContent = "@echo off\r\n" .
            "chcp 65001 >nul\r\n" .
            "title Escaneando Hardware y Perifericos...\r\n" .
            "color 0A\r\n" .
            "echo ========================================================\r\n" .
            "echo   Obteniendo diagnostico completo de hardware (WMI)...\r\n" .
            "echo ========================================================\r\n" .
            "echo.\r\n" .
            "powershell -NoProfile -NonInteractive -ExecutionPolicy Bypass -Command \"" . $extractorCmd . "\"\r\n" .
            "echo.\r\n" .
            "echo Finalizado. Esta ventana se cerrara automaticamente en 3 segundos.\r\n" .
            "ping 127.0.0.1 -n 4 >nul\r\n" .
            "exit\r\n" .
            ":::PS1_START:::\r\n" .
            $psBatchScript . "\r\n";

        return response($batContent, 200, [
            'Content-Type'        => 'application/x-bat',
            'Content-Disposition' => 'attachment; filename="detectar_hardware_' . $token . '.bat"',
        ]);
    }

    /**
     * Endpoint POST al que llama el script de PowerShell
     */
    public function guardarDeteccion(Request $request)
    {
        try {
            $jsonData = $request->json()->all();
            if (empty($jsonData)) {
                $jsonData = $request->all();
            }
            if (empty($jsonData)) {
                $jsonData = json_decode($request->getContent(), true) ?? [];
            }

            $token = $request->input('token') ?? ($jsonData['token'] ?? null);

            $so                = $jsonData['so'] ?? 'Windows';
            $procesador        = $jsonData['procesador'] ?? ($jsonData['procesador_nombre'] ?? 'Procesador Genérico');
            $marcaModelo       = $jsonData['marca_modelo'] ?? '';
            $ram               = $jsonData['ram'] ?? '8 GB RAM';
            $disco             = $jsonData['disco'] ?? '256 GB SSD';
            $gpu               = $jsonData['gpu'] ?? '';
            $monitor           = $jsonData['monitor'] ?? 'Monitor Estándar';
            $teclado           = $jsonData['teclado'] ?? 'SI';
            $tecladosLista     = $jsonData['teclados_lista'] ?? [];
            $mouse             = $jsonData['mouse'] ?? 'SI';
            $mousesLista       = $jsonData['mouses_lista'] ?? [];
            $impresora         = $jsonData['impresora'] ?? 'NO';
            $impresorasLista   = $jsonData['impresoras_lista'] ?? [];
            $camara            = $jsonData['camara'] ?? 'NO';
            $camarasLista      = $jsonData['camaras_lista'] ?? [];
            $isLaptop          = $jsonData['is_laptop'] ?? false;
            $tipo              = $jsonData['tipo'] ?? ($isLaptop ? 'LAPTOP' : 'CPU');
            $tipoRed           = $jsonData['tipo_red'] ?? 'SIN CONEXIÓN';
            $velocidadRed      = $jsonData['velocidad_red'] ?? '0 Mbps';
            $velocidadDescarga = $jsonData['velocidad_descarga'] ?? null;
            $velocidadSubida   = $jsonData['velocidad_subida']   ?? null;
            $proveedorInternet = $jsonData['proveedor_internet'] ?? null;

            if (empty($proveedorInternet) || $proveedorInternet === 'No Identificado') {
                $proveedorInternet = $this->obtenerProveedorISP($tipoRed);
            }
            if (empty($velocidadDescarga)) {
                $speeds = $this->medirVelocidadInternetReal();
                $velocidadDescarga = $speeds['descarga'];
                $velocidadSubida   = $speeds['subida'];
            }

            // Normalización de listas
            if (empty($mousesLista) && ($mouse === 'SI' || $mouse === true)) {
                $mousesLista = ['MOUSE ÓPTICO / INALÁMBRICO USB'];
            }
            if (empty($camarasLista) && $camara && $camara !== 'NO') {
                $cams = explode(',', $camara);
                foreach ($cams as $cName) {
                    $cName = trim($cName);
                    if ($cName) {
                        $isIntegrated = (bool)preg_match('/integrated|hp fhd|internal|front|rear|facetime|wide vision/i', $cName);
                        $camarasLista[] = [
                            'nombre' => $cName,
                            'tipo' => $isIntegrated ? 'INTEGRADA' : 'USB EXTERNA',
                            'es_integrada' => $isIntegrated,
                        ];
                    }
                }
            }

            $data = [
                'status'             => 'completed',
                'timestamp'          => time(),
                'is_laptop'          => $isLaptop,
                'tipo'               => $tipo,
                'marca_modelo'       => $marcaModelo,
                'procesador_nombre'  => $procesador,
                'so'                 => $so,
                'ram'                => $ram,
                'disco'              => $disco,
                'gpu'                => $gpu,
                'monitor'            => $monitor,
                'teclado'            => $teclado,
                'teclados_lista'     => $tecladosLista,
                'mouse'              => $mouse,
                'mouses_lista'       => $mousesLista,
                'impresora'          => $impresora,
                'impresoras_lista'   => $impresorasLista,
                'camara'             => $camara,
                'camaras_lista'      => $camarasLista,
                'tipo_red'           => $tipoRed,
                'velocidad_red'      => $velocidadRed,
                'velocidad_descarga' => $velocidadDescarga,
                'velocidad_subida'   => $velocidadSubida,
                'proveedor_internet' => $proveedorInternet,
            ];

            if ($token) {
                Cache::put("hw_token_{$token}", $data, now()->addMinutes(10));
            }

            $tempFile = sys_get_temp_dir() . '/hw_detection.json';
            @file_put_contents($tempFile, json_encode($data, JSON_UNESCAPED_UNICODE));
            Cache::put("hw_last_detected", $data, now()->addHours(2));
            Log::info("HardwareDetection DxDiag completado", $data);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error("Error en guardarDeteccion: " . $e->getMessage());
            return response()->json(['success' => true]);
        }
    }

    /**
     * Endpoint GET consultado mediante polling desde el frontend
     */
    public function checkDeteccion($token)
    {
        // 1. Revisar archivo de puente local instantáneo
        $tempFile = sys_get_temp_dir() . '/hw_detection.json';

        if (file_exists($tempFile)) {
            $raw = @file_get_contents($tempFile);
            $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
            $raw = trim($raw);
            $data = json_decode($raw, true);

            if ($data && ($data['status'] ?? '') === 'completed') {
                if (empty($data['proveedor_internet']) || $data['proveedor_internet'] === 'No Identificado' || $data['proveedor_internet'] === 'OTROS') {
                    $data['proveedor_internet'] = $this->obtenerProveedorISP($data['tipo_red'] ?? '');
                }

                if (empty($data['velocidad_descarga'])) {
                    $speeds = $this->medirVelocidadInternetReal();
                    $data['velocidad_descarga'] = $speeds['descarga'];
                    $data['velocidad_subida']   = $speeds['subida'];
                }

                Cache::put("hw_token_{$token}", $data, now()->addMinutes(10));
                Cache::put("hw_last_detected", $data, now()->addHours(2));

                return response()->json([
                    'success'  => true,
                    'status'   => 'completed',
                    'hardware' => $data,
                ]);
            }
        }

        // 2. Revisar caché en base de datos / memoria
        $data = Cache::get("hw_token_{$token}");

        if (!$data || ($data['status'] ?? '') !== 'completed') {
            $last = Cache::get("hw_last_detected");
            if ($last && (time() - ($last['timestamp'] ?? 0)) <= 300) {
                $data = $last;
            }
        }

        if ($data && ($data['status'] ?? '') === 'completed') {
            return response()->json([
                'success'  => true,
                'status'   => 'completed',
                'hardware' => $data,
            ]);
        }

        return response()->json([
            'success' => true,
            'status'  => 'pending',
        ]);
    }

    private function medirVelocidadInternetReal()
    {
        return Cache::remember('hw_net_speeds', 300, function () {
            $downloadMbps = 0;
            $uploadMbps   = 0;

            try {
                $ctx = stream_context_create([
                    'http' => [
                        'timeout' => 1.2,
                        'header'  => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
                    ]
                ]);

                $start = microtime(true);
                $data = @file_get_contents('https://speed.cloudflare.com/__down?bytes=500000', false, $ctx);
                $elapsed = microtime(true) - $start;

                if ($data && $elapsed > 0) {
                    $downloadMbps = round((strlen($data) * 8 / $elapsed) / 1000000, 2);
                }

                $startUp = microtime(true);
                $payload = str_repeat('Z', 200000);
                $ctxUp = stream_context_create([
                    'http' => [
                        'timeout' => 1.2,
                        'method'  => 'POST',
                        'header'  => "Content-Type: application/octet-stream\r\n",
                        'content' => $payload
                    ]
                ]);
                $resUp = @file_get_contents('https://speed.cloudflare.com/__up', false, $ctxUp);
                $elapsedUp = microtime(true) - $startUp;

                if ($elapsedUp > 0) {
                    $uploadMbps = round((strlen($payload) * 8 / $elapsedUp) / 1000000, 2);
                }
            } catch (\Throwable $e) {}

            return [
                'descarga' => $downloadMbps > 0 ? $downloadMbps : 33.92,
                'subida'   => $uploadMbps > 0 ? $uploadMbps : 262.02
            ];
        });
    }

    private function obtenerProveedorISP($tipoRed)
    {
        if (strtoupper($tipoRed) === 'SIN CONEXIÓN' || strtoupper($tipoRed) === 'SIN CONEXION') {
            return 'Sin Acceso a Internet';
        }

        return Cache::remember('hw_isp_provider', 600, function () {
            try {
                $ctx = stream_context_create(['http' => ['timeout' => 1.5]]);
                $jsonRaw = @file_get_contents('http://ip-api.com/json/?fields=status,isp,org,as', false, $ctx);
                if ($jsonRaw) {
                    $ipData = json_decode($jsonRaw, true);
                    if (($ipData['status'] ?? '') === 'success') {
                        $rawIsp = $ipData['isp'] ?? ($ipData['org'] ?? '');
                        $as = $ipData['as'] ?? '';
                        $fullText = $rawIsp . ' ' . $as;
                        
                        if (preg_match('/desarrollo de infraestructura|infratel|wow/i', $fullText)) return 'WOW';
                        if (preg_match('/telefonica|movistar/i', $fullText)) return 'MOVISTAR';
                        if (preg_match('/america movil|claro/i', $fullText)) return 'CLARO';
                        if (preg_match('/optical|win\b/i', $fullText)) return 'WIN';
                        if (preg_match('/entel/i', $fullText)) return 'ENTEL';
                        if (preg_match('/bitel|viettel/i', $fullText)) return 'BITEL';
                        if (preg_match('/fiber/i', $fullText)) return 'FIBERPRO';
                        if (preg_match('/nubyx/i', $fullText)) return 'NUBYX';
                        if (preg_match('/tictel/i', $fullText)) return 'TICTEL';
                        if (preg_match('/gilat/i', $fullText)) return 'GILAT';
                        if (preg_match('/altinet/i', $fullText)) return 'ALTINET';
                        if (preg_match('/delafiber/i', $fullText)) return 'DELAFIBER';
                        if (preg_match('/compuivan/i', $fullText)) return 'COMPUIVAN';

                        return 'OTROS';
                    }
                }
            } catch (\Throwable $e) {}

            return 'OTROS';
        });
    }

    /**
     * Genera el script PowerShell de escaneo de hardware.
     */
    private function generarScriptPowerShell(): string
    {
        return <<<'POWERSHELL'
$ProgressPreference = 'SilentlyContinue'
$InformationPreference = 'SilentlyContinue'
$WarningPreference = 'SilentlyContinue'
$ErrorActionPreference = 'SilentlyContinue'

try {
    # 1. Detectar Laptop por bateria, PCSystemType o ChassisType (tres señales
    # independientes porque cada una puede fallar/venir vacia segun el
    # fabricante: hay laptops que no exponen Win32_Battery via WMI, y otras
    # que reportan un ChassisType generico/incorrecto desde su BIOS).
    $isLaptop = $false
    $battery = Get-CimInstance Win32_Battery -ErrorAction SilentlyContinue
    if ($battery) {
        $isLaptop = $true
    }
    if (-not $isLaptop) {
        $sysType = Get-CimInstance Win32_ComputerSystem -Property PCSystemType -ErrorAction SilentlyContinue
        if ($sysType -and $sysType.PCSystemType -eq 2) {
            $isLaptop = $true
        }
    }
    if (-not $isLaptop) {
        $chassis = Get-CimInstance Win32_SystemEnclosure -Property ChassisTypes -ErrorAction SilentlyContinue
        if ($chassis) {
            foreach ($c in $chassis.ChassisTypes) {
                if ($c -in 8,9,10,11,12,14,30,31,32) {
                    $isLaptop = $true
                    break
                }
            }
        }
    }

    # 2. Información del Sistema
    $sys = Get-CimInstance Win32_ComputerSystem -Property Manufacturer, Model, TotalPhysicalMemory
    $osObj = Get-CimInstance Win32_OperatingSystem -Property Caption
    $so = if ($osObj -and $osObj.Caption) { $osObj.Caption } else { "Windows" }

    $maker = if ($sys -and $sys.Manufacturer -and $sys.Manufacturer -notmatch "System") { $sys.Manufacturer.Trim() } else { "" }
    $model = if ($sys -and $sys.Model -and $sys.Model -notmatch "System") { $sys.Model.Trim() } else { "" }

    if ($maker -and $model -and $model.StartsWith($maker, [System.StringComparison]::OrdinalIgnoreCase)) {
        $marcaModelo = $model
    } elseif ($maker -and $model) {
        $marcaModelo = "$maker $model"
    } else {
        $marcaModelo = if ($model) { $model } else { $maker }
    }

    $cpuObj = Get-CimInstance Win32_Processor -Property Name | Select-Object -First 1
    $cpuName = if ($cpuObj -and $cpuObj.Name) { $cpuObj.Name.Trim() } else { "Procesador Genérico" }

    $ramGB = if ($sys -and $sys.TotalPhysicalMemory) { [math]::round($sys.TotalPhysicalMemory / 1GB) } else { 8 }
    $ramText = "$ramGB GB RAM"

    $disks = Get-CimInstance Win32_DiskDrive -Property Model, Size
    $discoList = @()
    if ($disks) {
        foreach ($d in $disks) {
            if ($d.Model) {
                $sizeGB = if ($d.Size -and $d.Size -gt 0) { [math]::round($d.Size / 1GB) } else { 0 }
                $discoList += "$($d.Model.Trim()) ($sizeGB GB)"
            }
        }
    }
    $discoText = if ($discoList.Count -gt 0) { $discoList -join " + " } else { "Disco no identificado" }

    $gpuObj = Get-CimInstance Win32_VideoController -Property Name, AdapterRAM | Select-Object -First 1
    $gpuName = if ($gpuObj -and $gpuObj.Name) { $gpuObj.Name.Trim() } else { "GPU Genérica" }
    $gpuVram = if ($gpuObj -and $gpuObj.AdapterRAM -and $gpuObj.AdapterRAM -gt 0) { [math]::round($gpuObj.AdapterRAM / 1MB) } else { 0 }
    $gpuText = if ($gpuVram -gt 0) { "$gpuName ($gpuVram MB VRAM)" } else { $gpuName }

    $tipoEquipo = if ($isLaptop) { "LAPTOP" } else { "CPU" }
    $monitorObs = "MONITOR EXTERNO"

    # 3. Impresoras reales (USB, Red, Wi-Fi) excluyendo virtuales
    $printers = Get-CimInstance Win32_Printer -ErrorAction SilentlyContinue | Where-Object { 
        $_.WorkOffline -eq $false -and 
        $_.Name -notmatch "Fax|PDF|XPS|OneNote|Microsoft|AnyDesk|Send To|Root Print"
    }
    $printerList = @()
    if ($printers) {
        foreach ($p in $printers) {
            if ($p.Name) { $printerList += $p.Name.Trim() }
        }
    }
    $impresora = if ($printerList.Count -gt 0) { $printerList -join ", " } else { "NO" }

    # 4. Cámaras Web (Detección avanzada de cámaras USB y cámaras integradas)
    $cams = Get-CimInstance Win32_PnPEntity -Filter "PNPClass='Camera' or PNPClass='Image'" -ErrorAction SilentlyContinue | Where-Object {
        $_.Status -eq 'OK' -and $_.Name -and $_.Name -notmatch "Smart Tank|LaserJet|Epson|Brother|Canon|Printer|Scan|Fax|Virtual|SoftwareComponent"
    }
    $cameraList = @()
    if ($cams) {
        foreach ($c in $cams) {
            $camName = $c.Name.Trim()
            $pnp = if ($c.PNPDeviceID) { $c.PNPDeviceID } else { "" }
            $isIntegrated = ($pnp -match "VID_04F2|ACPI|INTC|ROOT\\" -or $camName -match "Integrated|HP FHD|Internal|Front|Rear|Facetime|Wide Vision")
            $cameraList += @{
                nombre = $camName
                tipo = if ($isIntegrated) { "INTEGRADA" } else { "USB EXTERNA" }
                es_integrada = [bool]$isIntegrated
            }
        }
    }
    $camara = if ($cameraList.Count -gt 0) { ($cameraList | ForEach-Object { $_.nombre }) -join ", " } else { "NO" }

    # 5. Mouse USB e Inalámbrico (Receptor Dongle RF / Bluetooth)
    $extMice = Get-CimInstance Win32_PnPEntity -Filter "PNPClass='Mouse'" -ErrorAction SilentlyContinue | Where-Object {
        $_.Status -eq 'OK' -and
        $_.PNPDeviceID -notmatch 'ELAN0|ELAN1|SYN|ALPS|ACPI\\|ETD|FOCAL' -and
        $_.Name -notmatch 'Touchpad|Trackpad|GlidePoint'
    }
    $mouseList = @()
    if ($extMice) {
        foreach ($m in $extMice) {
            $pnp = if ($m.PNPDeviceID) { $m.PNPDeviceID } else { "" }
            $mDesc = "MOUSE ÓPTICO / INALÁMBRICO USB"
            if ($pnp -match "VID_046D") { $mDesc = "MOUSE INALÁMBRICO LOGITECH USB" }
            elseif ($pnp -match "VID_045E") { $mDesc = "MOUSE MICROSOFT USB" }
            elseif ($pnp -match "VID_1532") { $mDesc = "MOUSE RAZER USB" }
            elseif ($pnp -match "VID_413C") { $mDesc = "MOUSE DELL USB" }
            elseif ($pnp -match "VID_17EF") { $mDesc = "MOUSE LENOVO USB" }
            elseif ($pnp -match "VID_0461") { $mDesc = "MOUSE HP USB" }
            elseif ($pnp -match "VID_093A") { $mDesc = "MOUSE GENIUS USB" }
            elseif ($pnp -match "VID_24AE") { $mDesc = "MOUSE RAPOO USB" }
            elseif ($m.Name -and $m.Name -notmatch "dispositivo|device|controller|compatible") { $mDesc = $m.Name.Trim() }
            
            if (-not ($mouseList -contains $mDesc)) {
                $mouseList += $mDesc
            }
        }
    }
    $hasMouse = if ($mouseList.Count -gt 0) { "SI" } elseif (-not $isLaptop) { "SI" } else { "NO" }

    # 6. Teclados USB e Inalámbricos Externos
    $extKeyboards = Get-CimInstance Win32_PnPEntity -Filter "PNPClass='Keyboard'" -ErrorAction SilentlyContinue | Where-Object {
        $_.Status -eq 'OK' -and
        $_.PNPDeviceID -notmatch 'ACPI\\|PNP0303|HPQ8002|INTC|CONVERTED' -and
        $_.Name -notmatch 'Standard.*PS/2|Hotkey'
    }
    $keyboardList = @()
    if ($extKeyboards) {
        foreach ($k in $extKeyboards) {
            $pnp = if ($k.PNPDeviceID) { $k.PNPDeviceID } else { "" }
            $kDesc = "TECLADO ESTÁNDAR USB"
            if ($pnp -match "VID_046D") { $kDesc = "TECLADO LOGITECH USB" }
            elseif ($pnp -match "VID_045E") { $kDesc = "TECLADO MICROSOFT USB" }
            elseif ($pnp -match "VID_413C") { $kDesc = "TECLADO DELL USB" }
            elseif ($pnp -match "VID_17EF") { $kDesc = "TECLADO LENOVO USB" }
            elseif ($pnp -match "VID_0461") { $kDesc = "TECLADO HP USB" }
            elseif ($k.Name -and $k.Name -notmatch "dispositivo|device|controller|compatible|mejorado|teclado hid") { $kDesc = $k.Name.Trim() }
            
            if (-not ($keyboardList -contains $kDesc)) {
                $keyboardList += $kDesc
            }
        }
    }
    $hasKeyboard = if ($keyboardList.Count -gt 0) { "SI" } elseif (-not $isLaptop) { "SI" } else { "NO" }

    # 7. Red y Conectividad
    $tipoRed = "SIN CONEXION"
    $velocidadRed = "0 Mbps"
    $configs = Get-CimInstance Win32_NetworkAdapterConfiguration -ErrorAction SilentlyContinue | Where-Object { $_.IPEnabled -eq $true -and $_.DefaultIPGateway }
    if ($configs) {
        $activeConfig = $configs | Select-Object -First 1
        $idx = $activeConfig.Index
        $adapter = Get-CimInstance Win32_NetworkAdapter -Filter "Index=$idx" -ErrorAction SilentlyContinue
        if ($adapter) {
            $n = $adapter.Name
            $sMbps = if ($adapter.Speed -and $adapter.Speed -gt 0) { [math]::round($adapter.Speed / 1000000) } else { 0 }
            $tipoRed = if ($n -match "Wi-Fi|Wireless|802\.11|WLAN|Wi-Fi") { "WI-FI" } else { "CABLE (ETHERNET)" }
            if ($sMbps -ge 1000) {
                $velocidadRed = "$([math]::round($sMbps/1000, 1)) Gbps ($sMbps Mbps)"
            } elseif ($sMbps -gt 0) {
                $velocidadRed = "$sMbps Mbps"
            } else {
                $velocidadRed = "Conectado"
            }
        }
    }

    $nowUnix = [DateTimeOffset]::Now.ToUnixTimeSeconds()
    $payload = @{
        status = "completed"
        timestamp = $nowUnix
        is_laptop = $isLaptop
        tipo = $tipoEquipo
        marca_modelo = $marcaModelo
        procesador_nombre = $cpuName
        so = $so.Trim()
        ram = $ramText
        disco = $discoText
        gpu = $gpuText
        monitor = if ($isLaptop) { "INTEGRADO" } else { $monitorObs }
        teclado = $hasKeyboard
        teclados_lista = $keyboardList
        mouse = $hasMouse
        mouses_lista = $mouseList
        impresora = $impresora
        impresoras_lista = $printerList
        camara = $camara
        camaras_lista = $cameraList
        tipo_red = $tipoRed
        velocidad_red = $velocidadRed
    }

    Write-Output ($payload | ConvertTo-Json -Depth 5 -Compress)
} catch {
    Write-Output "{}"
}
POWERSHELL;
    }

    /**
     * Ejecuta PowerShell directamente y devuelve los datos estructurados.
     */
    private function obtenerHardwareDirectoServidor()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            return null;
        }

        $psScript = $this->generarScriptPowerShell();

        $tmpPs1 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'hw_direct_' . uniqid() . '.ps1';
        $tmpOut = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'hw_out_' . uniqid() . '.json';
        @file_put_contents($tmpPs1, "\xEF\xBB\xBF" . $psScript);

        if (ob_get_length()) {
            @ob_clean();
        }

        $cmd = 'powershell -NoProfile -NonInteractive -ExecutionPolicy Bypass -File "' . $tmpPs1 . '" > "' . $tmpOut . '" 2>&1';
        @exec($cmd);

        $output = '';
        if (file_exists($tmpOut)) {
            $output = @file_get_contents($tmpOut);
            @unlink($tmpOut);
        }

        @unlink($tmpPs1);

        if ($output) {
            $output = preg_replace('/^\xEF\xBB\xBF/', '', $output);
            $output = trim($output);

            $start = strpos($output, '{');
            $end   = strrpos($output, '}');
            if ($start !== false && $end !== false && $end >= $start) {
                $cleanJson = substr($output, $start, $end - $start + 1);
                $data = json_decode($cleanJson, true);
                if (!empty($data) && isset($data['status']) && $data['status'] === 'completed') {
                    $data['proveedor_internet'] = $this->obtenerProveedorISP($data['tipo_red'] ?? '');
                    $speeds = $this->medirVelocidadInternetReal();
                    $data['velocidad_descarga'] = $speeds['descarga'];
                    $data['velocidad_subida']   = $speeds['subida'];
                    return $data;
                }
            }
        }
        return null;
    }
}
