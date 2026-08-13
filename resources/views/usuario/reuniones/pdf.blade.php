<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Reunión Nº {{ str_pad($reunion->id, 4, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            margin: 25px 45px 35px 45px; 
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10.5px;
            color: #000;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border: 1px solid #000;
        }
        td, th {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .bg-blue {
            background-color: #17365d;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 8px;
        }
        ol {
            margin-top: 5px;
            margin-bottom: 5px;
            padding-left: 20px;
        }
        li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <div class="title">ACTA DE REUNIÓN</div>

    <!-- CABECERA -->
    <table>
        <tr>
            <td colspan="2" class="text-center font-bold" style="width: 70%; font-size: 11px;">
                {{ mb_strtoupper($reunion->titulo_reunion, 'UTF-8') }}
            </td>
            <td class="bg-blue" style="width: 10%;">FECHA</td>
            <td class="text-center font-bold" style="width: 20%;">
                {{ \Carbon\Carbon::parse($reunion->fecha_reunion)->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td class="bg-blue" style="width: 15%;">INSTITUCIÓN</td>
            <td class="text-center font-bold" style="width: 55%;">
                {{ mb_strtoupper($reunion->nombre_institucion, 'UTF-8') }}
            </td>
            <td class="bg-blue" style="width: 10%;">HORA</td>
            <td class="text-center font-bold" style="width: 20%;">
                {{ \Carbon\Carbon::parse($reunion->hora_reunion)->format('h:i A') }}
            </td>
        </tr>
    </table>

    <!-- INFORMACION GENERAL -->
    <table>
        <tr>
            <td class="bg-blue">INFORMACIÓN GENERAL</td>
        </tr>
        <tr>
            <td style="text-align: justify; padding: 10px; font-size: 12px;"> <!-- 1 punto más -->
                {{ $reunion->descripcion_general }}
            </td>
        </tr>
    </table>

    <!-- PARTICIPANTES -->
    <table>
        <tr>
            <td colspan="4" class="bg-blue">PARTICIPANTES</td>
        </tr>
        <tr>
            <td class="text-center font-bold" style="width: 28%;">PARTICIPANTE</td>
            <td class="text-center font-bold" style="width: 18.5%;">CARGO</td>
            <td class="text-center font-bold" style="width: 18.5%;">ENTIDAD</td>
            <td class="text-center font-bold" style="width: 35%;">FIRMA</td>
        </tr>
        @if(empty($reunion->participantes))
            <tr><td colspan="4" class="text-center">SIN PARTICIPANTES REGISTRADOS</td></tr>
        @else
            @foreach($reunion->participantes as $p)
            <tr>
                <td>
                    {{ mb_strtoupper(($p['tipo_documento'] ?? 'DNI').' '.$p['dni'].' - '.($p['apellidos'] ?? '') . ' ' . ($p['nombres'] ?? ''), 'UTF-8') }}
                    @if(isset($p['celular']) && $p['celular'])
                        <br><span style="font-size: 8px; color: #555;">CEL: {{ $p['celular'] }}</span>
                    @endif
                </td>
                <td class="text-center">{{ mb_strtoupper($p['cargo'] ?? '', 'UTF-8') }}</td>
                <td class="text-center">{{ mb_strtoupper($p['institucion'] ?? '', 'UTF-8') }}</td>
                <td style="height: 65px; vertical-align: bottom; text-align: center; padding: 5px;"></td>
            </tr>
            @endforeach
        @endif
    </table>

    <!-- ACUERDOS -->
    <table>
        <tr>
            <td class="bg-blue">ACUERDOS</td>
        </tr>
        <tr>
            <td style="text-align: justify; padding: 10px; font-size: 12px;">
                @if(!empty($reunion->acuerdos))
                    <ol>
                        @foreach($reunion->acuerdos as $ac)
                            <li>{{ $ac['descripcion'] }}</li>
                        @endforeach
                    </ol>
                @else
                    <div class="text-center">SIN ACUERDOS RELEVANTES.</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- OBSERVACIONES -->
    <table>
        <tr>
            <td class="bg-blue">COMENTARIOS / OBSERVACIONES</td>
        </tr>
        <tr>
            <td style="text-align: justify; padding: 10px; font-size: 12px;">
                @if(!empty($reunion->comentarios_observaciones))
                    <ol>
                        @foreach($reunion->comentarios_observaciones as $ob)
                            <li>{{ $ob['descripcion'] }}</li>
                        @endforeach
                    </ol>
                @else
                    <div class="text-center">SIN OBSERVACIONES RELEVANTES.</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- PROCESAMIENTO DE IMÁGENES -->
    @php
        $imageFields = ['foto_1', 'foto_2'];
        $imagenesSrc = [];
        foreach ($imageFields as $field) {
            if (!empty($reunion->$field)) {
                $fotoPath = $reunion->$field;
                
                // Si por algún motivo el nombre de la foto se guardó solo como archivo, sin el directorio
                if (strpos($fotoPath, '/') === false) {
                    $fotoPath = 'storage/reuniones/' . $fotoPath;
                }
                
                $posiblesRutas = [
                    public_path($fotoPath), // Si el symlink existe en public/storage/...
                    storage_path('app/public/' . str_replace('storage/', '', $fotoPath)), // Local en storage/app/public/...
                    storage_path('app/private/public/reuniones/' . basename($fotoPath)), // Archivos ocultos que se guardaron accidentalmente aquí
                    storage_path('app/public/reuniones/' . basename($fotoPath)) // Fallback seguro
                ];
                
                $rutaEncontrada = null;
                foreach ($posiblesRutas as $ruta) {
                    if (file_exists($ruta) && is_file($ruta)) {
                        $rutaEncontrada = $ruta;
                        break;
                    }
                }
                
                if($rutaEncontrada){
                    $type = pathinfo($rutaEncontrada, PATHINFO_EXTENSION);
                    $data = file_get_contents($rutaEncontrada);
                    $imagenesSrc[] = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }
        }
    @endphp

    @if(count($imagenesSrc) > 0)
    <div style="margin-top: 10px; page-break-inside: avoid;">
        <div class="bg-blue" style="border: 1px solid #000; border-bottom: none; padding: 4px 6px;">EVIDENCIA FOTOGRÁFICA</div>
        <div class="text-center" style="border: 1px solid #000; padding: 10px;">
            <table style="width: 100%; border: none; margin: 0; padding: 0;">
                <tr>
                    @foreach($imagenesSrc as $src)
                        <td style="width: 50%; text-align: center; border: none; padding: 5px;">
                            <img src="{{ $src }}" style="max-width: 95%; max-height: 250px; width: auto; height: auto; display: inline-block; vertical-align: top;">
                        </td>

                    @endforeach
                </tr>
            </table>
        </div>
        @if($reunion->hora_finalizada_reunion)
        @php
            $hCb = \Carbon\Carbon::parse($reunion->hora_finalizada_reunion);
            $hNum = (int)$hCb->format('g');
            $mNum = (int)$hCb->format('i');
            $hAmPm = $hCb->format('A');

            $numerosTexto = [
                0 => 'cero', 1 => 'una', 2 => 'dos', 3 => 'tres', 4 => 'cuatro', 5 => 'cinco', 6 => 'seis', 7 => 'siete', 8 => 'ocho', 9 => 'nueve', 10 => 'diez',
                11 => 'once', 12 => 'doce', 13 => 'trece', 14 => 'catorce', 15 => 'quince', 16 => 'dieciséis', 17 => 'diecisiete', 18 => 'dieciocho', 19 => 'diecinueve', 20 => 'veinte',
                21 => 'veintiuno', 22 => 'veintidós', 23 => 'veintitrés', 24 => 'veinticuatro', 25 => 'veinticinco', 26 => 'veintiséis', 27 => 'veintisiete', 28 => 'veintiocho', 29 => 'veintinueve', 30 => 'treinta',
                31 => 'treinta y uno', 32 => 'treinta y dos', 33 => 'treinta y tres', 34 => 'treinta y cuatro', 35 => 'treinta y cinco', 36 => 'treinta y seis', 37 => 'treinta y siete', 38 => 'treinta y ocho', 39 => 'treinta y nueve', 40 => 'cuarenta',
                41 => 'cuarenta y uno', 42 => 'cuarenta y dos', 43 => 'cuarenta y tres', 44 => 'cuarenta y cuatro', 45 => 'cuarenta y cinco', 46 => 'cuarenta y seis', 47 => 'cuarenta y siete', 48 => 'cuarenta y ocho', 49 => 'cuarenta y nueve', 50 => 'cincuenta',
                51 => 'cincuenta y uno', 52 => 'cincuenta y dos', 53 => 'cincuenta y tres', 54 => 'cincuenta y cuatro', 55 => 'cincuenta y cinco', 56 => 'cincuenta y seis', 57 => 'cincuenta y siete', 58 => 'cincuenta y ocho', 59 => 'cincuenta y nueve'
            ];

            $hTxt = $numerosTexto[$hNum] ?? $hNum;
            $art = $hNum == 1 ? 'la' : 'las';
            
            $mTxt = '';
            if ($mNum > 0) {
                if ($mNum == 30) $mTxt = ' y media';
                else $mTxt = ' y ' . ($numerosTexto[$mNum] ?? $mNum) . ' minutos';
            } else {
                $mTxt = ' en punto';
            }

            $pTxt = $hAmPm == 'AM' ? ' de la mañana' : ($hNum >= 6 && $hNum != 12 ? ' de la noche' : ' de la tarde');
            if ($hNum == 12 && $hAmPm == 'PM') {
                $pTxt = ' del mediodía';
                if ($mNum == 0) $mTxt = '';
            }
            $textoHora = trim("$art $hTxt$mTxt$pTxt");
        @endphp
        <div style="border: 1px solid #000; border-top: none; padding: 5px; font-size: 12px;">
            Siendo {{ $textoHora }}, se da por concluida la reunión, firmando los presentes.
        </div>
        @endif
    </div>
    @endif


    <script type="text/php">
        if (isset($pdf)) {
            $x = $pdf->get_width() - 85; 
            $y = $pdf->get_height() - 25; // Subido ligeramente para que no se pierda al imprimir
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $font = $fontMetrics->get_font("Arial", "bold");
            $size = 10;
            
            // Lado izquierdo
            $pdf->page_text(45, $y, "ACTA DE REUNIÓN", $font, $size);
            
            // Lado derecho
            $pdf->page_text($x, $y, "{PAGE_NUM}/{PAGE_COUNT}", $font, $size);
        }
    </script>
</body>
</html>
