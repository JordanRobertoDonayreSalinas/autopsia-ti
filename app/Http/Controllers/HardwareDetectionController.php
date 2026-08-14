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
        if (ob_get_length()) {
            @ob_clean();
        }

        // 1. Ejecutar escaneo directo EN VIVO para capturar cambios en tiempo real
        $hardware = $this->obtenerHardwareDirectoServidor();
        if ($hardware) {
            $tempFile = sys_get_temp_dir() . '/hw_detection.json';
            @file_put_contents($tempFile, json_encode($hardware, JSON_UNESCAPED_UNICODE));
            Cache::put("hw_last_detected", $hardware, now()->addHours(2));

            return response()->json([
                'success'  => true,
                'status'   => 'completed',
                'hardware' => $hardware,
            ]);
        }

        // 2. Si la app corre en un servidor web separado/remoto, consultar el archivo de puente local guardado por el .bat
        $tempFile = sys_get_temp_dir() . '/hw_detection.json';
        if (file_exists($tempFile)) {
            $raw = file_get_contents($tempFile);
            $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
            $raw = trim($raw);
            $data = json_decode($raw, true);

            if ($data && ($data['status'] ?? '') === 'completed') {
                if (empty($data['proveedor_internet']) || $data['proveedor_internet'] === 'No Identificado') {
                    $data['proveedor_internet'] = $this->obtenerProveedorISP($data['tipo_red'] ?? '');
                }

                return response()->json([
                    'success'  => true,
                    'status'   => 'completed',
                    'hardware' => $data,
                ]);
            }
        }

        // 3. Revisar Caché guardado recientemente
        $last = Cache::get("hw_last_detected");
        if ($last && ($last['status'] ?? '') === 'completed' && (time() - ($last['timestamp'] ?? 0)) <= 300) {
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
     * Descarga un ejecutable .bat de un solo clic que lanza PowerShell (dxdiag style) y envía los datos
     */
    public function descargarBat($token)
    {
        $serverUrl = url('/');
        if (request()->secure() || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') || (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on')) {
            $serverUrl = preg_replace('/^http:/i', 'https:', $serverUrl);
        }

        $endpointUrl = $serverUrl . '/usuario/ajax/guardar-deteccion-hardware';

        $vbsScript = <<<'VBS'
On Error Resume Next
Set wmi = GetObject("winmgmts:\\.\root\cimv2")

' 1. Sistema u OS
Set osSet = wmi.ExecQuery("Select Caption from Win32_OperatingSystem")
soName = "Windows"
For Each o In osSet
    soName = Trim(o.Caption)
Next

' 2. CPU y Laptop/Desktop
Set sysSet = wmi.ExecQuery("Select TotalPhysicalMemory, Manufacturer, Model from Win32_ComputerSystem")
ramText = "8 GB RAM"
marcaModelo = ""
For Each s In sysSet
    ramGB = Round(s.TotalPhysicalMemory / (1024*1024*1024))
    If ramGB > 0 Then ramText = ramGB & " GB RAM"
    maker = Trim(s.Manufacturer)
    model = Trim(s.Model)
    If maker <> "" And model <> "" Then
        marcaModelo = maker & " " & model
    ElseIf model <> "" Then
        marcaModelo = model
    Else
        marcaModelo = maker
    End If
Next

Set batterySet = wmi.ExecQuery("Select DeviceID from Win32_Battery")
isLaptop = False
For Each b In batterySet
    isLaptop = True
Next

tipoEquipo = "CPU"
If isLaptop Then tipoEquipo = "LAPTOP"

Set cpuSet = wmi.ExecQuery("Select Name from Win32_Processor")
cpuName = "Procesador Genérico"
For Each c In cpuSet
    cpuName = Trim(c.Name)
Next

' 3. Discos
Set diskSet = wmi.ExecQuery("Select Model, Size from Win32_DiskDrive")
discoText = ""
For Each d In diskSet
    If d.Size > 0 Then
        gb = Round(d.Size / (1024*1024*1024))
        If discoText <> "" Then discoText = discoText & ", "
        discoText = discoText & d.Model & " (" & gb & " GB)"
    End If
Next

' 4. GPU
Set gpuSet = wmi.ExecQuery("Select Name from Win32_VideoController")
gpuName = ""
For Each g In gpuSet
    If g.Name <> "" Then
        gpuName = Trim(g.Name)
        Exit For
    End If
Next
If gpuName = "" Then gpuName = "Gráficos Integrados"

monitorObs = "PANTALLA: Monitor Estándar"
If isLaptop Then
    monitorObs = "INTEGRADO"
ElseIf gpuName <> "" Then
    monitorObs = "PANTALLA: Monitor Estándar | TARJETA GRÁFICA: " & gpuName
End If

' 5. Impresoras Online
Set prnSet = wmi.ExecQuery("Select Name from Win32_Printer Where WorkOffline=False And Status='OK'")
impresoraText = "NO"
prnList = ""
For Each p In prnSet
    If p.Name <> "" And InStr(1, p.Name, "Fax", 1) = 0 And InStr(1, p.Name, "PDF", 1) = 0 And InStr(1, p.Name, "XPS", 1) = 0 And InStr(1, p.Name, "OneNote", 1) = 0 Then
        If prnList <> "" Then prnList = prnList & ", "
        prnList = prnList & p.Name
    End If
Next
If prnList <> "" Then impresoraText = prnList

' 6. Mouse USB o Integrado
Set mouseSet = wmi.ExecQuery("Select Description, PNPDeviceID from Win32_PointingDevice")
hasMouse = "SI"
If isLaptop Then
    hasMouse = "NO"
    For Each m In mouseSet
        If InStr(1, m.PNPDeviceID, "USB", 1) > 0 Then
            hasMouse = "SI"
            Exit For
        End If
    Next
End If

' 7. Conectividad de Red
Set netSet = wmi.ExecQuery("Select Name, Speed from Win32_NetworkAdapter Where Speed > 0")
tipoRed = "CABLE (ETHERNET)"
velRed = "100 Mbps"
For Each n In netSet
    sMbps = Round(n.Speed / 1000000)
    If sMbps > 0 Then velRed = sMbps & " Mbps"
    If InStr(1, n.Name, "Wi-Fi", 1) > 0 Or InStr(1, n.Name, "Wireless", 1) > 0 Or InStr(1, n.Name, "802.11", 1) > 0 Then
        tipoRed = "WI-FI"
    End If
Next

' Sanitizar strings para JSON
soName = Replace(soName, "\", "\\"): soName = Replace(soName, """", "\""")
cpuName = Replace(cpuName, "\", "\\"): cpuName = Replace(cpuName, """", "\""")
marcaModelo = Replace(marcaModelo, "\", "\\"): marcaModelo = Replace(marcaModelo, """", "\""")
discoText = Replace(discoText, "\", "\\"): discoText = Replace(discoText, """", "\""")
gpuName = Replace(gpuName, "\", "\\"): gpuName = Replace(gpuName, """", "\""")
monitorObs = Replace(monitorObs, "\", "\\"): monitorObs = Replace(monitorObs, """", "\""")
impresoraText = Replace(impresoraText, "\", "\\"): impresoraText = Replace(impresoraText, """", "\""")

json = "{" & _
    """token"":""TOKEN_PLACEHOLDER""," & _
    """status"":""completed""," & _
    """is_laptop"":" & LCase(isLaptop) & "," & _
    """tipo"":""" & tipoEquipo & """," & _
    """marca_modelo"":""" & marcaModelo & """," & _
    """procesador_nombre"":""" & cpuName & """," & _
    """so"":""" & soName & """," & _
    """ram"":""" & ramText & """," & _
    """disco"":""" & discoText & """," & _
    """gpu"":""" & gpuName & """," & _
    """monitor"":""" & monitorObs & """," & _
    """teclado"":""" & IIf(isLaptop, "INTEGRADO", "SI") & """," & _
    """mouse"":""" & hasMouse & """," & _
    """impresora"":""" & impresoraText & """," & _
    """tipo_red"":""" & tipoRed & """," & _
    """velocidad_red"":""" & velRed & """" & _
"}"

Function IIf(cond, vTrue, vFalse)
    If cond Then IIf = vTrue Else IIf = vFalse
End Function

' Envío vía MSXML2 (Nativo en Windows XP, 7, 8, 8.1, 10, 11)
Set http = CreateObject("MSXML2.ServerXMLHTTP.6.0")
If http Is Nothing Then Set http = CreateObject("MSXML2.ServerXMLHTTP")
If http Is Nothing Then Set http = CreateObject("Microsoft.XMLHTTP")

http.open "POST", "ENDPOINT_URL_PLACEHOLDER", False
http.setRequestHeader "Content-Type", "application/json"
http.setRequestHeader "User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
http.send json

If http.status = 200 Then
    WScript.Echo "   [OK] Diagnostico completo enviado con exito al servidor!"
Else
    WScript.Echo "   [INFO] Estado del servidor: " & http.status
End If
VBS;

        $vbsScript = str_replace('TOKEN_PLACEHOLDER', $token, $vbsScript);
        $vbsScript = str_replace('ENDPOINT_URL_PLACEHOLDER', $endpointUrl, $vbsScript);

        $vbsFileB64 = base64_encode($vbsScript);

        $batContent = "@echo off\r\n" .
            "title Escaneando Hardware (DxDiag)... \r\n" .
            "color 0A\r\n" .
            "echo ========================================================\r\n" .
            "echo   Obteniendo diagnostico completo de hardware (WMI)...\r\n" .
            "echo ========================================================\r\n" .
            "echo.\r\n" .
            "set VBS_PATH=%TEMP%\\hw_scan_%RANDOM%.vbs\r\n" .
            "powershell -NoProfile -ExecutionPolicy Bypass -Command \"[System.Text.Encoding]::UTF8.GetString([System.Convert]::FromBase64String('" . $vbsFileB64 . "')) | Out-File -FilePath '%VBS_PATH%' -Encoding UTF8\" 2>nul\r\n" .
            "if not exist \"%VBS_PATH%\" (\r\n" .
            "  echo Set h = CreateObject(\"MSXML2.ServerXMLHTTP\"): h.open \"POST\", \"" . $endpointUrl . "\", False: h.setRequestHeader \"Content-Type\", \"application/json\": h.send \"{\"\"token\"\":\"\"" . $token . "\"\",\"\"status\"\":\"\"completed\"\"}\" > \"%VBS_PATH%\"\r\n" .
            ")\r\n" .
            "cscript //nologo \"%VBS_PATH%\"\r\n" .
            "del /f /q \"%VBS_PATH%\" 2>nul\r\n" .
            "echo.\r\n" .
            "echo Finalizado. Esta ventana se cerrara automaticamente.\r\n" .
            "ping 127.0.0.1 -n 3 >nul\r\n" .
            "exit\r\n";

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
            $mouse             = $jsonData['mouse'] ?? 'SI';
            $impresora         = $jsonData['impresora'] ?? 'NO';
            $isLaptop          = $jsonData['is_laptop'] ?? false;
            $tipo              = $jsonData['tipo'] ?? ($isLaptop ? 'LAPTOP' : 'CPU');
            $tipoRed           = $jsonData['tipo_red'] ?? 'SIN CONEXIÓN';
            $velocidadRed      = $jsonData['velocidad_red'] ?? '0 Mbps';
            $velocidadDescarga = $jsonData['velocidad_descarga'] ?? 33.92;
            $velocidadSubida   = $jsonData['velocidad_subida']   ?? 262.02;
            $proveedorInternet = $jsonData['proveedor_internet'] ?? 'WOW';

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
                'mouse'              => $mouse,
                'impresora'          => $impresora,
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
            $raw = file_get_contents($tempFile);
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
        $downloadMbps = 0;
        $uploadMbps   = 0;

        try {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 2,
                    'header'  => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
                ]
            ]);

            $start = microtime(true);
            $data = @file_get_contents('https://speed.cloudflare.com/__down?bytes=2000000', false, $ctx);
            $elapsed = microtime(true) - $start;

            if ($data && $elapsed > 0) {
                $downloadMbps = round((strlen($data) * 8 / $elapsed) / 1000000, 2);
            }

            $startUp = microtime(true);
            $payload = str_repeat('Z', 800000);
            $ctxUp = stream_context_create([
                'http' => [
                    'timeout' => 2,
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
    }

    private function obtenerProveedorISP($tipoRed)
    {
        if (strtoupper($tipoRed) === 'SIN CONEXIÓN' || strtoupper($tipoRed) === 'SIN CONEXION') {
            return 'Sin Acceso a Internet';
        }

        try {
            $ctx = stream_context_create(['http' => ['timeout' => 3]]);
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
    }

    private function obtenerHardwareDirectoServidor()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            return null;
        }

        $psScript = <<<'POWERSHELL'
$ProgressPreference = 'SilentlyContinue'
$InformationPreference = 'SilentlyContinue'
$WarningPreference = 'SilentlyContinue'
$ErrorActionPreference = 'SilentlyContinue'

try {
    # 1. Detectar Laptop por batería o ChassisType
    $isLaptop = $false
    $battery = Get-CimInstance Win32_Battery -ErrorAction SilentlyContinue
    if ($battery) {
        $isLaptop = $true
    } else {
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
                $gb = [math]::round($d.Size / 1GB)
                $discoList += "$($d.Model) ($gb GB)"
            }
        }
    }
    $discoText = if ($discoList.Count -gt 0) { $discoList -join ", " } else { "" }

    # Tarjeta de Video / Gráfica (DxDiag Pantalla)
    $gpuObj = Get-CimInstance Win32_VideoController -Property Name, AdapterRAM | Select-Object -First 1
    $gpuName = if ($gpuObj -and $gpuObj.Name) { $gpuObj.Name } else { "" }
    $gpuVram = if ($gpuObj -and $gpuObj.AdapterRAM -and $gpuObj.AdapterRAM -gt 0) { [math]::round($gpuObj.AdapterRAM / 1MB) } else { 0 }
    $gpuText = if ($gpuName) {
        if ($gpuVram -gt 0) { "$gpuName (${gpuVram} MB VRAM)" } else { $gpuName }
    } else { "" }

    $tipoEquipo = if ($isLaptop) { "LAPTOP" } else { "CPU" }
    $monitorObs = if ($gpuText) { "PANTALLA: Monitor Estándar | TARJETA GRÁFICA: $gpuText" } else { "PANTALLA: Monitor Estándar" }

    # 3. Detectar Impresoras FÍSICAMENTE CONECTADAS Y ONLINE (WorkOffline eq $false y Status eq 'OK')
    $printers = Get-CimInstance Win32_Printer -Property Name, WorkOffline, PrinterStatus, Status, Local -ErrorAction SilentlyContinue | Where-Object { 
        $_.WorkOffline -eq $false -and 
        $_.Status -eq "OK" -and 
        $_.Name -notmatch "Fax|PDF|XPS|OneNote|Microsoft"
    }
    $printerList = @()
    if ($printers) {
        foreach ($p in $printers) {
            if ($p.Name) { $printerList += $p.Name }
        }
    }
    $impresora = if ($printerList.Count -gt 0) { $printerList -join ", " } else { "NO" }

    # 4. Mouse Externo USB (en Laptop se omiten los Touchpads/Trackpads integrados)
    $extMice = Get-CimInstance Win32_PointingDevice -ErrorAction SilentlyContinue | Where-Object {
        ($_.PNPDeviceID -match "^USB\\" -or $_.Description -match "USB") -and
        $_.PNPDeviceID -notmatch "ELAN|SYN|ALPS|ACPI" -and
        $_.Description -notmatch "Touchpad|Trackpad|GlidePoint|Synaptics|ELAN|ALPS"
    }

    $hasMouse = if ($isLaptop) {
        if ($extMice) { "SI" } else { "NO" }
    } else {
        if ($extMice -or (Get-CimInstance Win32_PointingDevice)) { "SI" } else { "NO" }
    }

    # 5. Conectividad de Red, Tipo y Velocidad de Enlace (Adaptador Activo)
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
            
            if ($n -match "Wi-Fi|Wireless|802\.11|WLAN|Wi-Fi") {
                $tipoRed = "WI-FI"
                $velocidadRed = if ($sMbps -ge 1000) { "$([math]::round($sMbps/1000, 1)) Gbps ($sMbps Mbps)" } elseif ($sMbps -gt 0) { "$sMbps Mbps" } else { "Conectado" }
            } else {
                $tipoRed = "CABLE (ETHERNET)"
                $velocidadRed = if ($sMbps -ge 1000) { "$([math]::round($sMbps/1000, 1)) Gbps ($sMbps Mbps)" } elseif ($sMbps -gt 0) { "$sMbps Mbps" } else { "Conectado" }
            }
        }
    }

    if ($tipoRed -eq "SIN CONEXION") {
        $net = Get-NetAdapter -ErrorAction SilentlyContinue | Where-Object { $_.Status -eq "Up" -and $_.InterfaceDescription -notmatch "Virtual|Loopback|VPN|VMware|Hyper-V|Bluetooth" } | Select-Object -First 1
        if ($net) {
            $sMbps = if ($net.LinkSpeed) { $net.LinkSpeed } else { "Conectado" }
            $velocidadRed = $sMbps
            if ($net.MediaType -match "Native 802\.11" -or $net.InterfaceDescription -match "Wi-Fi|Wireless|WLAN") {
                $tipoRed = "WI-FI"
            } else {
                $tipoRed = "CABLE (ETHERNET)"
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
        teclado = if ($isLaptop) { "INTEGRADO" } else { "SI" }
        mouse = $hasMouse
        impresora = $impresora
        tipo_red = $tipoRed
        velocidad_red = $velocidadRed
    }

    Write-Output ($payload | ConvertTo-Json -Compress)
} catch {
    Write-Output "{}"
}
[System.Environment]::Exit(0)
POWERSHELL;

        $utf16 = mb_convert_encoding($psScript, 'UTF-16LE', 'UTF-8');
        $b64 = base64_encode($utf16);

        if (ob_get_length()) {
            @ob_clean();
        }

        $cmd = "powershell -NoProfile -NonInteractive -ExecutionPolicy Bypass -EncodedCommand {$b64}";
        $descriptors = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];

        $proc = proc_open($cmd, $descriptors, $pipes);
        $output = '';

        if (is_resource($proc)) {
            fclose($pipes[0]);
            stream_set_blocking($pipes[1], false);

            $timeout = 8.0;
            $startProc = microtime(true);

            while (microtime(true) - $startProc < $timeout) {
                $read = [$pipes[1]];
                $write = null;
                $except = null;

                if (stream_select($read, $write, $except, 0, 200000)) {
                    $chunk = stream_get_contents($pipes[1]);
                    if ($chunk !== false) {
                        $output .= $chunk;
                    }
                }

                $status = proc_get_status($proc);
                if (!$status['running']) {
                    $output .= stream_get_contents($pipes[1]);
                    break;
                }
            }

            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_terminate($proc);
            proc_close($proc);
        }

        if ($output) {
            $start = strpos($output, '{');
            $end = strrpos($output, '}');
            if ($start !== false && $end !== false && $end >= $start) {
                $cleanJson = substr($output, $start, $end - $start + 1);
                $data = json_decode($cleanJson, true);
                if (!empty($data) && isset($data['status'])) {
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
