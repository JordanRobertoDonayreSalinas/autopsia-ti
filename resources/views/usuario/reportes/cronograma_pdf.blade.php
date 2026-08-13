<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cronograma de Actividades</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        th {
            background-color: #1E3A5F;
            color: #FFFFFF;
            font-weight: bold;
            text-align: center;
            border: 1px solid #AAAAAA;
            padding: 5px;
        }
        td {
            background-color: #FFFFFF;
            border: 1px solid #AAAAAA;
            padding: 5px;
            vertical-align: middle;
        }
        .td-semana {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            vertical-align: middle;
            width: 10%;
        }
        .tr-mes td {
            background-color: #2E6DA4;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 11px;
            text-align: center;
            padding: 6px;
            border: 1px solid #1E3A5F;
        }
        .text-center { text-align: center; }
        .evidencia-img { max-width: 100px; max-height: 80px; margin-right: 5px; margin-bottom: 5px; }
        .evidencia-container { margin-top: 5px; }
    </style>
</head>
<body>
    @php
        use Carbon\Carbon;

        $inicioFmt = Carbon::parse($fechaInicio)->format('d/m/Y');
        $finFmt    = Carbon::parse($fechaFin)->format('d/m/Y');

        // ── Función para construir semanas de un mes ──
        $buildSemanasDelMes = function(int $year, int $month, array $filasDelMes): array {
            $cursor = Carbon::create($year, $month, 1)->startOfDay();
            $finMes = Carbon::create($year, $month, 1)->endOfMonth()->startOfDay();
            $numSem = 1;
            $semanas = [];

            while ($cursor->lte($finMes)) {
                $finSemana = $cursor->copy()->endOfWeek(Carbon::SUNDAY)->startOfDay();

                // Si día 1 es domingo, endOfWeek = mismo día → extender al siguiente domingo
                if ($finSemana->isSameDay($cursor)) {
                    $finSemana = $cursor->copy()->addWeek()->endOfWeek(Carbon::SUNDAY)->startOfDay();
                }
                if ($finSemana->gt($finMes)) {
                    $finSemana = $finMes->copy();
                }

                $semanas[] = [
                    'semana' => $numSem,
                    'desde'  => $cursor->copy(),
                    'hasta'  => $finSemana->copy(),
                    'filas'  => [],
                ];
                $cursor = $finSemana->copy()->addDay();
                $numSem++;
            }

            // Asignar actividades a su semana
            foreach ($filasDelMes as $fila) {
                $fecha = Carbon::parse($fila['fecha'])->startOfDay();
                foreach ($semanas as &$s) {
                    if ($fecha->between($s['desde'], $s['hasta'])) {
                        $s['filas'][] = $fila;
                        break;
                    }
                }
                unset($s);
            }

            return array_values(array_filter($semanas, fn($s) => count($s['filas']) > 0));
        };

        // ── Agrupar actividades por mes ──
        $porMes = [];
        foreach ($actividades as $fila) {
            $key = Carbon::parse($fila['fecha'])->format('Y-m');
            $porMes[$key][] = $fila;
        }
        ksort($porMes);

        // ── Construir estructura mes → semanas ──
        $meses = [];
        foreach ($porMes as $yearMonth => $filasDelMes) {
            [$year, $month] = explode('-', $yearMonth);
            $semanas = $buildSemanasDelMes((int)$year, (int)$month, $filasDelMes);
            if (count($semanas) > 0) {
                $meses[] = [
                    'mes'     => mb_strtoupper(Carbon::create((int)$year, (int)$month, 1)->locale('es')->isoFormat('MMMM YYYY'), 'UTF-8'),
                    'semanas' => $semanas,
                ];
            }
        }
    @endphp

    <h2 style="text-align: center; color: #1E3A5F; margin-bottom: 20px;">
        CRONOGRAMA DE ACTIVIDADES DEL {{ $inicioFmt }} AL {{ $finFmt }}
    </h2>

    <table>
        <thead>
            <tr>
                <th style="width: 9%;">SEMANAS</th>
                <th style="width: 9%;">FECHA DE ACTIVIDAD</th>
                <th style="width: 22%;">DESCRIPCIÓN DE ACTIVIDAD</th>
                <th style="width: 18%;">ESTABLECIMIENTO EN DONDE SE REALIZA LA ACTIVIDAD</th>
                <th style="width: 20%;">PARTICIPANTES EN LA ACTIVIDAD</th>
                <th style="width: 22%;">ACTA O EVIDENCIA DE LA ACTIVIDAD REALIZADA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($meses as $mesGrupo)
                {{-- Fila separadora de mes --}}
                <tr class="tr-mes">
                    <td colspan="6">{{ $mesGrupo['mes'] }}</td>
                </tr>

                @foreach($mesGrupo['semanas'] as $grupo)
                    @php $totalFilas = count($grupo['filas']); @endphp
                    @foreach($grupo['filas'] as $idx => $fila)
                        @php
                            $fecha = Carbon::parse($fila['fecha'])->format('d/m/Y');

                            if ($fila['tipo_key'] === 'asistencia') {
                                $actividadTxt = $fila['actividad'] !== '—' ? $fila['actividad'] : '';
                                $actividadTxt = mb_strtolower($actividadTxt, 'UTF-8');
                                $actividadTxt = mb_strtoupper(mb_substr($actividadTxt, 0, 1, 'UTF-8'), 'UTF-8')
                                              . mb_substr($actividadTxt, 1, null, 'UTF-8');
                                $descripcion = 'Asistencia Técnica: ' . $actividadTxt;
                                if (!empty($fila['modalidad']) && $fila['modalidad'] !== '—') {
                                    $descripcion .= '<br>Modalidad: ' . $fila['modalidad'];
                                }
                            } elseif ($fila['tipo_key'] === 'monitoreo') {
                                $descripcion = 'Monitoreo de Uso del SIHCE MINSA - Presencial';
                            } elseif ($fila['tipo_key'] === 'reunion') {
                                $actividadTxt = $fila['actividad'] !== '—' ? $fila['actividad'] : '';
                                $descripcion  = mb_convert_case(mb_strtolower($actividadTxt, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                            } else {
                                $descripcion = 'Implementación Módulo de ' . $fila['actividad'];
                            }

                            if ($fila['tipo_key'] === 'reunion') {
                                // Para reuniones: solo nombre_institucion, sin prefijo ni provincia
                                $nombreEstab = $fila['establecimiento'];
                                if (mb_strtoupper($nombreEstab, 'UTF-8') === $nombreEstab) {
                                    $nombreEstab = mb_convert_case($nombreEstab, MB_CASE_TITLE, 'UTF-8');
                                }
                                $estabTxt = $nombreEstab;
                            } else {
                                $categoria   = strtoupper(trim($fila['categoria_establecimiento'] ?? ''));
                                $prefijo     = in_array($categoria, ['I-1','I-2']) ? 'P.S.' : (in_array($categoria, ['I-3','I-4']) ? 'C.S.' : '');
                                $nombreEstab = $fila['establecimiento'];
                                if (mb_strtoupper($nombreEstab, 'UTF-8') === $nombreEstab) {
                                    $nombreEstab = mb_convert_case($nombreEstab, MB_CASE_TITLE, 'UTF-8');
                                }
                                $estabTxt = ($prefijo ? $prefijo . ' ' : '') . $nombreEstab . ' - ' . $fila['provincia'];
                            }

                            $participantesTxt = $fila['participantes_txt'] ?? $fila['responsable'];
                            $actaTxt          = $fila['nombre_acta'] ?? ('Acta de ' . $fila['tipo']);

                            $semanaLabel = "SEMANA {$grupo['semana']}:\nDEL "
                                . $grupo['desde']->format('d/m')
                                . ' AL '
                                . $grupo['hasta']->format('d/m');

                            $isFirst = ($idx === 0);
                            $isLast  = ($idx === $totalFilas - 1);
                            // Mostrar el texto en la fila del medio del grupo
                            $midIdx  = (int) floor($totalFilas / 2);
                            $isMid   = ($idx === $midIdx);

                            // Simular fusión con bordes (DomPDF no soporta rowspan cross-page)
                            if ($isFirst && $isLast) {
                                $semanaBorder = 'border: 1px solid #AAAAAA;';
                            } elseif ($isFirst) {
                                $semanaBorder = 'border: 1px solid #AAAAAA; border-bottom: 1px solid transparent;';
                            } elseif ($isLast) {
                                $semanaBorder = 'border: 1px solid #AAAAAA; border-top: 1px solid transparent;';
                            } else {
                                $semanaBorder = 'border-left: 1px solid #AAAAAA; border-right: 1px solid #AAAAAA; border-top: 1px solid transparent; border-bottom: 1px solid transparent;';
                            }
                        @endphp
                        <tr>
                            <td class="td-semana" style="{{ $semanaBorder }}">
                                @if($isMid)
                                    {!! nl2br(e($semanaLabel)) !!}
                                @endif
                            </td>
                            <td class="text-center">{{ $fecha }}</td>
                            <td>{!! $descripcion !!}</td>
                            <td class="text-center">{{ $estabTxt }}</td>
                            <td>{!! nl2br(e($participantesTxt)) !!}</td>
                            <td>
                                @if($fila['tipo_key'] === 'reunion')
                                    {{-- Para reuniones: texto en parte superior, fotos abajo --}}
                                    <div style="text-align:center; margin-bottom:6px;">{{ $actaTxt }}</div>
                                    @if(!empty($fila['imagenes_paths']) && is_array($fila['imagenes_paths']))
                                        <div style="text-align:center;">
                                            @foreach($fila['imagenes_paths'] as $imgPath)
                                                @if(file_exists($imgPath))
                                                    @php
                                                        $type   = pathinfo($imgPath, PATHINFO_EXTENSION);
                                                        $data   = file_get_contents($imgPath);
                                                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                                    @endphp
                                                    <img src="{{ $base64 }}" style="max-width:120px; max-height:90px; margin:2px; border:1px solid #ccc;">
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    <div style="margin-bottom: 8px;">{{ $actaTxt }}</div>
                                    @if(!empty($fila['imagenes_paths']) && is_array($fila['imagenes_paths']))
                                        <div class="evidencia-container">
                                            @foreach($fila['imagenes_paths'] as $imgPath)
                                                @if(file_exists($imgPath))
                                                    @php
                                                        $type   = pathinfo($imgPath, PATHINFO_EXTENSION);
                                                        $data   = file_get_contents($imgPath);
                                                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                                    @endphp
                                                    <img src="{{ $base64 }}" class="evidencia-img">
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
