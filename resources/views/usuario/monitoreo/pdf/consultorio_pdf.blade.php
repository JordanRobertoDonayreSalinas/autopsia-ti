<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Evaluación de Consultorio: {{ $contenido['titulo_consultorio'] ?? 'Consultorio' }} - Acta #{{ $acta->numero_acta }}</title>
    <style>
        /* ═══════════════════════════════════════════════════════
           EVALUACIÓN DE CONSULTORIO — DISEÑO COMPACTO Y ELEGANTE
           Tipografía: DejaVu Sans (nativa TrueType de DomPDF)
           ═══════════════════════════════════════════════════════ */
        @page { 
            margin: 0.7cm 1cm 1.2cm 1cm; 
        }

        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 8px; 
            color: #1e293b; 
            line-height: 1.25; 
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* ── BARRA SUPERIOR DECORATIVA ── */
        .top-accent {
            height: 3.5px;
            background-color: #4f46e5;
            margin-bottom: 7px;
        }

        /* ── ENCABEZADO PRINCIPAL ── */
        .header-block {
            margin-bottom: 6px;
        }
        .header-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .header-grid td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .tag-pill {
            background-color: #4f46e5;
            color: #ffffff;
            font-size: 6.5px;
            font-weight: bold;
            padding: 2.5px 8px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        .tag-acta {
            color: #64748b;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            margin-left: 6px;
            letter-spacing: 0.3px;
        }
        .header-title {
            font-size: 13.5px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: -0.3px;
            margin: 3px 0 1.5px 0;
        }
        .header-subtitle {
            font-size: 7.5px;
            color: #475569;
            line-height: 1.2;
        }
        .header-subtitle strong {
            color: #1e293b;
        }

        /* Tarjetas de resumen en encabezado */
        .summary-cards {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4px 0;
        }
        .summary-cards td {
            border: none;
            padding: 0;
        }
        .stat-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 4px 6px;
            text-align: center;
        }
        .stat-card-accent {
            background-color: #eef2ff;
            border: 1px solid #c7d2fe;
        }
        .stat-value {
            font-size: 11px;
            font-weight: bold;
            color: #4f46e5;
            display: block;
            line-height: 1.1;
            text-transform: uppercase;
        }
        .stat-label {
            font-size: 6px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            display: block;
            margin-top: 1px;
        }

        .header-divider {
            border: none;
            height: 1px;
            background-color: #e2e8f0;
            margin: 5px 0 7px 0;
        }

        /* ── SECCIONES DEL REPORTE ── */
        .section-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 8px;
            margin-bottom: 6px;
        }
        .section-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 3px;
        }
        .section-header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        
        /* Número de sección cuadrado redondeado para alineación vertical perfecta en DomPDF */
        .section-badge {
            background-color: #4f46e5;
            color: #ffffff;
            font-size: 7.5px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
            display: inline-block;
            text-align: center;
            line-height: 1;
        }
        .section-title-text {
            font-size: 8.5px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            margin-left: 5px;
        }

        /* ── GRID DE CAMPOS FORMULARIO ── */
        .form-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px 0;
            margin-left: -5px;
            margin-right: -5px;
        }
        .form-grid td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .field-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 4px 6px;
        }
        .field-label {
            font-size: 6px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 1.5px;
            display: block;
        }
        .field-value {
            font-size: 7.5px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }

        /* ── BADGES & ESTILOS DE ESTADO ── */
        .badge {
            display: inline-block;
            padding: 1.5px 5px;
            border-radius: 3px;
            font-size: 6.5px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            letter-spacing: 0.2px;
        }
        .badge-operativo { background-color: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .badge-regular { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-inoperativo { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .badge-propio { background-color: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; }

        /* ── TABLA DE EQUIPOS ── */
        table.equipos-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
            margin-bottom: 4px;
        }
        table.equipos-table th {
            background-color: #4f46e5;
            color: #ffffff;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 5px;
            text-align: left;
            border: 1px solid #4f46e5;
            letter-spacing: 0.2px;
        }
        table.equipos-table td {
            border: 1px solid #e2e8f0;
            padding: 3.5px 5px;
            font-size: 7.5px;
            vertical-align: middle;
            color: #334155;
        }
        table.equipos-table tr:nth-child(even) {
            background-color: #fafbff;
        }

        /* ── CAJA DXDIAG (SPECS TÉCNICAS) ── */
        .dxdiag-card {
            margin-top: 4px;
            background-color: #fafbff;
            border: 1px solid #c7d2fe;
            border-left: 3px solid #4f46e5;
            border-radius: 5px;
            padding: 5px 7px;
        }
        .dxdiag-title {
            font-size: 7px;
            font-weight: bold;
            color: #4338ca;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 3px;
        }
        table.dxdiag-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.dxdiag-table td {
            border: 1px solid #e0e7ff;
            background: #ffffff;
            padding: 3px 5px;
            vertical-align: top;
        }
        .dx-label {
            font-size: 5.5px;
            font-weight: bold;
            color: #64748b;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            margin-bottom: 1px;
        }
        .dx-val {
            font-size: 7px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }

        /* ── TARJETAS CONECTIVIDAD ── */
        .conn-card {
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 4px 6px;
            background-color: #ffffff;
            text-align: center;
        }
        .conn-card-active {
            border: 1.5px solid #4f46e5;
            background-color: #eef2ff;
        }
        .conn-title {
            font-size: 7.5px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .conn-sub {
            font-size: 6px;
            font-weight: bold;
            color: #4f46e5;
            text-transform: uppercase;
            margin-top: 1px;
        }

        /* ── EVIDENCIA FOTOGRÁFICA ── */
        .evidence-card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background-color: #fafbff;
            padding: 5px;
            text-align: center;
            margin-top: 4px;
        }
        .evidence-img {
            max-width: 100%;
            max-height: 125px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
        }
        .evidence-caption {
            font-size: 7px;
            font-weight: bold;
            color: #4f46e5;
            text-transform: uppercase;
            margin-top: 3px;
            letter-spacing: 0.3px;
        }

        /* ── OBSERVACIONES CARD ── */
        .obs-box {
            background-color: #fafbff;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #4f46e5;
            border-radius: 3px;
            padding: 5px 8px;
            font-size: 7.5px;
            color: #334155;
            line-height: 1.25;
        }

        /* ── FOOTER FIJO ── */
        .footer-fixed {
            position: fixed;
            bottom: -0.75cm;
            left: 0;
            right: 0;
        }
        .footer-inner {
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
            width: 100%;
        }
        .footer-text {
            font-size: 6.5px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
    </style>
</head>
<body>

    {{-- BARRA DECORATIVA SUPERIOR --}}
    <div class="top-accent"></div>

    @php 
        $secNum = 1; 
        $contenido = $detalle->contenido ?? [];
        if (is_string($contenido)) {
            $contenido = json_decode($contenido, true) ?? [];
        }
        $equipos = $equipos ?? \App\Models\EquipoComputo::where('cabecera_monitoreo_id', $acta->id)
            ->where('modulo', $detalle->modulo_nombre ?? '')
            ->get();
    @endphp

    {{-- ═══ ENCABEZADO PRINCIPAL ═══ --}}
    <div class="header-block">
        <table class="header-grid">
            <tr>
                {{-- COLUMNA IZQUIERDA --}}
                <td style="width: 68%;">
                    <div>
                        <span class="tag-pill">Módulo de Evaluación</span>
                        <span class="tag-acta">Acta N° <strong>#{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}</strong></span>
                    </div>
                    <div class="header-title">{{ $contenido['titulo_consultorio'] ?? 'EVALUACIÓN DE CONSULTORIO' }}</div>
                    <div class="header-subtitle">
                        <strong>IPRESS:</strong> {{ $acta->establecimiento->codigo ?? 'S/C' }} — {{ strtoupper($acta->establecimiento->nombre ?? 'NO REGISTRADO') }}
                        &nbsp;&bull;&nbsp;
                        <strong>Red:</strong> {{ strtoupper($acta->establecimiento->red ?? 'General') }}
                        @if(!empty($acta->establecimiento->microred))
                            &nbsp;&bull;&nbsp; <strong>Microred:</strong> {{ strtoupper($acta->establecimiento->microred) }}
                        @endif
                        @if(!empty($acta->establecimiento->provincia))
                            &nbsp;&bull;&nbsp; <strong>Provincia:</strong> {{ strtoupper($acta->establecimiento->provincia) }}
                        @endif
                    </div>
                </td>

                {{-- COLUMNA DERECHA: TARJETAS METRICAS --}}
                <td style="width: 32%;">
                    <table class="summary-cards">
                        <tr>
                            <td style="width: 50%;">
                                <div class="stat-card stat-card-accent">
                                    <span class="stat-value">{{ isset($contenido['fecha']) ? date('d/m/Y', strtotime($contenido['fecha'])) : date('d/m/Y') }}</span>
                                    <span class="stat-label">Fecha Monitoreo</span>
                                </div>
                            </td>
                            <td style="width: 50%;">
                                <div class="stat-card">
                                    <span class="stat-value" style="color: #334155;">{{ $contenido['turno'] ?? 'MAÑANA' }}</span>
                                    <span class="stat-label">Turno Evaluado</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <hr class="header-divider">

    {{-- ═══ 1. DATOS GENERALES DEL CONSULTORIO ═══ --}}
    <div class="section-card">
        <table class="section-header-table">
            <tr>
                <td style="width: 20px;"><span class="section-badge">{{ $secNum++ }}</span></td>
                <td><span class="section-title-text">DATOS GENERALES DEL CONSULTORIO / MÓDULO</span></td>
            </tr>
        </table>

        {{-- FILA 1: Denominación y Servicio --}}
        <table class="form-grid" style="margin-bottom: 4px;">
            <tr>
                <td style="width: {{ !empty($contenido['servicio_asociado']) ? '50%' : '100%' }};">
                    <div class="field-box" style="border-color: #c7d2fe; background: #eef2ff;">
                        <span class="field-label" style="color: #4f46e5;">Denominación del Consultorio / Módulo</span>
                        <span class="field-value" style="color: #312e81; font-size: 8px;">
                            {{ $contenido['titulo_consultorio'] ?? 'CONSULTORIO' }}
                        </span>
                    </div>
                </td>
                @if(!empty($contenido['servicio_asociado']))
                <td style="width: 50%;">
                    <div class="field-box" style="border-color: #c7d2fe; background: #eef2ff;">
                        <span class="field-label" style="color: #4f46e5;">Servicio del Consultorio</span>
                        <span class="field-value" style="color: #312e81; font-size: 8px;">
                            {{ strtoupper($contenido['servicio_asociado']) }}
                        </span>
                    </div>
                </td>
                @endif
            </tr>
        </table>

        {{-- FILA 2: Tipo, Piso, Electricidad y Puntos de Red --}}
        <table class="form-grid">
            <tr>
                <td style="width: 25%;">
                    <div class="field-box">
                        <span class="field-label">Tipo de Consultorio</span>
                        <span class="field-value">
                            {{ $contenido['tipo_consultorio'] ?? 'FÍSICO' }}
                        </span>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="field-box">
                        <span class="field-label">Ubicación / Piso</span>
                        <span class="field-value">
                            {{ is_numeric($contenido['piso'] ?? '') ? ('PISO ' . $contenido['piso']) : ($contenido['piso'] ?? 'PISO 1') }}
                        </span>
                    </div>
                </td>
                <td style="width: 25%;">
                    @php $elec = strtoupper($contenido['cuenta_electricidad'] ?? 'SI'); @endphp
                    <div class="field-box" style="{{ $elec === 'SI' ? 'border-color: #86efac; background: #f0fdf4;' : 'border-color: #fca5a5; background: #fef2f2;' }}">
                        <span class="field-label" style="{{ $elec === 'SI' ? 'color: #166534;' : 'color: #991b1b;' }}">Electricidad</span>
                        <span class="field-value" style="{{ $elec === 'SI' ? 'color: #166534;' : 'color: #991b1b;' }}">
                            {{ $elec === 'SI' ? '✓ CUENTA (SÍ)' : '✗ NO CUENTA' }}
                        </span>
                    </div>
                </td>
                <td style="width: 25%;">
                    @php 
                        $pred = strtoupper($contenido['cuenta_punto_red'] ?? 'SI'); 
                        $cantPuntos = $contenido['cantidad_puntos_red'] ?? $contenido['puntos_red_cantidad'] ?? null;
                    @endphp
                    <div class="field-box" style="{{ $pred === 'SI' ? 'border-color: #86efac; background: #f0fdf4;' : 'border-color: #fca5a5; background: #fef2f2;' }}">
                        <span class="field-label" style="{{ $pred === 'SI' ? 'color: #166534;' : 'color: #991b1b;' }}">Puntos de Red</span>
                        <span class="field-value" style="{{ $pred === 'SI' ? 'color: #166534;' : 'color: #991b1b;' }}">
                            @if($pred === 'SI')
                                ✓ SÍ ({{ !empty($cantPuntos) ? ($cantPuntos . ' ' . ((int)$cantPuntos === 1 ? 'PUNTO' : 'PUNTOS')) : 'HABILITADO' }})
                            @else
                                ✗ NO HABILITADO
                            @endif
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        @if(strtoupper($contenido['requiere_mas_puntos_red'] ?? 'NO') === 'SI')
            @php $cantPuntosReq = $contenido['cantidad_puntos_red_requerido'] ?? 1; @endphp
            <div style="margin-top: 6px; background-color: #fffbeb; border: 1.5px solid #fcd34d; border-radius: 5px; padding: 6px 8px; font-size: 7px; color: #92400e;">
                <strong style="text-transform: uppercase;">⚠ Requiere implementar {{ $cantPuntosReq }} {{ (int)$cantPuntosReq === 1 ? 'punto de red adicional' : 'puntos de red adicionales' }}</strong>
                @if(!empty($contenido['observacion_requerimiento_punto_red']))
                    <br>{{ strtoupper($contenido['observacion_requerimiento_punto_red']) }}
                @endif
            </div>
        @endif
    </div>

    {{-- ═══ 2. EQUIPOS DE CÓMPUTO E IMPRESORA ═══ --}}
    <div class="section-card">
        <table class="section-header-table">
            <tr>
                <td style="width: 20px;"><span class="section-badge">{{ $secNum++ }}</span></td>
                <td><span class="section-title-text">EQUIPOS DE CÓMPUTO E IMPRESORA</span></td>
            </tr>
        </table>

        @if(count($equipos) > 0)
            <table class="equipos-table">
                <thead>
                    <tr>
                        <th style="width: 18px; text-align: center;">#</th>
                        <th style="width: 135px;">Descripción</th>
                        <th style="width: 32px; text-align: center;">Cant.</th>
                        <th style="width: 70px; text-align: center;">Estado</th>
                        <th style="width: 70px; text-align: center;">Propiedad</th>
                        <th style="width: 95px;">N° Serie / C.Pat</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($equipos as $idx => $eq)
                        @php
                            $est = strtoupper(trim($eq->estado ?? 'OPERATIVO'));
                            $estClass = 'badge-operativo';
                            if ($est === 'REGULAR') $estClass = 'badge-regular';
                            if ($est === 'INOPERATIVO') $estClass = 'badge-inoperativo';
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: bold; color: #64748b;">{{ $idx + 1 }}</td>
                            <td style="font-weight: bold; color: #0f172a; text-transform: uppercase;">
                                {{ $eq->descripcion }}
                            </td>
                            <td style="text-align: center; font-weight: bold; color: #1e293b;">
                                {{ $eq->cantidad ?? 1 }}
                            </td>
                            <td style="text-align: center;">
                                <span class="badge {{ $estClass }}">{{ $est }}</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-propio">{{ strtoupper($eq->propio ?? 'EXCLUSIVO') }}</span>
                            </td>
                            <td style="font-weight: bold; color: #4338ca; font-size: 7px;">
                                {{ !empty($eq->nro_serie) ? strtoupper($eq->nro_serie) : 'S/N' }}
                            </td>
                            <td style="color: #475569; font-size: 7px;">
                                {{ !empty($eq->observacion) ? strtoupper($eq->observacion) : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- ESPECIFICACIONES TÉCNICAS DXDIAG --}}
            @php
                $dxdiagEquipo = null;
                foreach ($equipos as $eqItem) {
                    $descUpper = str_replace('-', ' ', strtoupper(trim($eqItem->descripcion ?? '')));
                    $esComputo = str_contains($descUpper, 'CPU') ||
                                 str_contains($descUpper, 'LAPTOP') ||
                                 str_contains($descUpper, 'ALL IN ONE') ||
                                 str_contains($descUpper, 'AIO') ||
                                 str_contains($descUpper, 'COMPUTADORA') ||
                                 str_contains($descUpper, 'COMPUTADOR') ||
                                 str_contains($descUpper, 'PC');
                    if ($esComputo && !empty($eqItem->especificaciones)) {
                        $specs = is_array($eqItem->especificaciones) ? $eqItem->especificaciones : json_decode($eqItem->especificaciones, true);
                        if (!empty($specs) && is_array($specs)) {
                            $dxdiagEquipo = $specs;
                            break;
                        }
                    }
                }
            @endphp

            @if(!empty($dxdiagEquipo))
                <div class="dxdiag-card">
                    <div class="dxdiag-title">
                        ⚡ ESPECIFICACIONES TÉCNICAS DEL EQUIPO PRINCIPAL (DIAGNÓSTICO DXDIAG)
                    </div>
                    <table class="dxdiag-table">
                        <tr>
                            <td style="width: 33.33%;">
                                <span class="dx-label">Modelo / Equipo:</span>
                                <span class="dx-val">{{ $dxdiagEquipo['modelo'] ?? 'NO IDENTIFICADO' }}</span>
                            </td>
                            <td style="width: 33.33%;">
                                <span class="dx-label">Procesador:</span>
                                <span class="dx-val">{{ $dxdiagEquipo['procesador'] ?? 'NO IDENTIFICADO' }}</span>
                            </td>
                            <td style="width: 33.33%;">
                                <span class="dx-label">Memoria RAM:</span>
                                <span class="dx-val">{{ $dxdiagEquipo['ram'] ?? '--' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="dx-label">Almacenamiento:</span>
                                <span class="dx-val">{{ $dxdiagEquipo['disco'] ?? '--' }}</span>
                            </td>
                            <td>
                                <span class="dx-label">Tarjeta Gráfica (GPU):</span>
                                <span class="dx-val">{{ $dxdiagEquipo['gpu'] ?? '--' }}</span>
                            </td>
                            <td>
                                <span class="dx-label">Sistema Operativo:</span>
                                <span class="dx-val">{{ $dxdiagEquipo['so'] ?? '--' }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            @endif
        @else
            <div style="background-color: #fafbff; border: 1.5px dashed #cbd5e1; border-radius: 5px; padding: 8px; text-align: center; color: #94a3b8; font-size: 7.5px; font-weight: bold; text-transform: uppercase;">
                No se registraron equipos de cómputo en este consultorio.
            </div>
        @endif
    </div>

    {{-- ═══ REQUERIMIENTO DE EQUIPOS ═══ --}}
    @if(!empty($requerimientos) && count($requerimientos) > 0)
        <div class="section-card">
            <table class="section-header-table">
                <tr>
                    <td style="width: 20px;"><span class="section-badge">{{ $secNum++ }}</span></td>
                    <td><span class="section-title-text">REQUERIMIENTO DE EQUIPOS</span></td>
                </tr>
            </table>

            <table class="equipos-table">
                <thead>
                    <tr>
                        <th style="width: 18px; text-align: center;">#</th>
                        <th style="width: 160px;">Tipo de Equipo</th>
                        <th style="width: 40px; text-align: center;">Cant.</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requerimientos as $idx => $req)
                        <tr>
                            <td style="text-align: center; font-weight: bold; color: #64748b;">{{ $idx + 1 }}</td>
                            <td style="font-weight: bold; color: #0f172a; text-transform: uppercase;">
                                {{ $req->descripcion }}
                            </td>
                            <td style="text-align: center; font-weight: bold; color: #1e293b;">
                                {{ $req->cantidad ?? 1 }}
                            </td>
                            <td style="color: #475569; font-size: 7px;">
                                {{ !empty($req->observacion) ? strtoupper($req->observacion) : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ═══ 3. TIPO DE CONECTIVIDAD ═══ --}}
    @php
        $hasComputoPdf = false;
        if (isset($equipos) && count($equipos) > 0) {
            foreach ($equipos as $eq) {
                $descUpper = str_replace('-', ' ', strtoupper(trim($eq->descripcion ?? '')));
                if (
                    str_contains($descUpper, 'CPU') ||
                    str_contains($descUpper, 'LAPTOP') ||
                    str_contains($descUpper, 'COMPUTADORA') ||
                    str_contains($descUpper, 'COMPUTADOR') ||
                    str_contains($descUpper, 'ALL IN ONE') ||
                    str_contains($descUpper, 'AIO') ||
                    str_contains($descUpper, 'PC')
                ) {
                    $hasComputoPdf = true;
                    break;
                }
            }
        }
        $tipoConn = strtoupper(trim($contenido['tipo_conectividad'] ?? ''));
    @endphp

    @if($hasComputoPdf || !empty($tipoConn))
    <div class="section-card">
        <table class="section-header-table">
            <tr>
                <td style="width: 20px;"><span class="section-badge">{{ $secNum++ }}</span></td>
                <td><span class="section-title-text">TIPO DE CONECTIVIDAD Y ACCESO A INTERNET</span></td>
            </tr>
        </table>

        {{-- TARJETAS DE SELECCIÓN --}}
        <table class="form-grid" style="margin-bottom: 4px;">
            <tr>
                <td style="width: 33.33%;">
                    <div class="conn-card {{ $tipoConn === 'WIFI' ? 'conn-card-active' : '' }}">
                        <div class="conn-title" style="{{ $tipoConn === 'WIFI' ? 'color: #4338ca;' : '' }}">WIFI</div>
                        <div class="conn-sub">Inalámbrico</div>
                    </div>
                </td>
                <td style="width: 33.33%;">
                    <div class="conn-card {{ $tipoConn === 'CABLEADO' ? 'conn-card-active' : '' }}">
                        <div class="conn-title" style="{{ $tipoConn === 'CABLEADO' ? 'color: #4338ca;' : '' }}">CABLEADO</div>
                        <div class="conn-sub" style="{{ $tipoConn === 'CABLEADO' ? 'color: #4338ca;' : 'color: #64748b;' }}">Ethernet</div>
                    </div>
                </td>
                <td style="width: 33.34%;">
                    <div class="conn-card {{ $tipoConn === 'SIN CONECTIVIDAD' ? 'conn-card-active' : '' }}">
                        <div class="conn-title" style="{{ $tipoConn === 'SIN CONECTIVIDAD' ? 'color: #dc2626;' : '' }}">SIN CONECTIVIDAD</div>
                        <div class="conn-sub" style="color: #dc2626;">Sin Internet</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- DETALLES DE CONECTIVIDAD --}}
        <table class="form-grid">
            <tr>
                @if($tipoConn === 'WIFI')
                    <td style="width: 25%;">
                        <div class="field-box">
                            <span class="field-label">Fuente de WiFi</span>
                            <span class="field-value" style="color: #4338ca;">
                                {{ !empty($contenido['wifi_fuente']) ? strtoupper($contenido['wifi_fuente']) : 'ESTABLECIMIENTO' }}
                            </span>
                        </div>
                    </td>
                @endif
                <td style="width: {{ $tipoConn === 'WIFI' ? '25%' : '34%' }};">
                    <div class="field-box">
                        <span class="field-label">Operador (ISP)</span>
                        <span class="field-value">
                            {{ !empty($contenido['operador_servicio']) ? strtoupper($contenido['operador_servicio']) : ($tipoConn === 'SIN CONECTIVIDAD' ? 'N/A' : 'NO ESPECIFICADO') }}
                        </span>
                    </div>
                </td>
                <td style="width: {{ $tipoConn === 'WIFI' ? '25%' : '33%' }};">
                    <div class="field-box">
                        <span class="field-label">Velocidad Descarga</span>
                        <span class="field-value" style="color: #059669;">
                            @if(!empty($contenido['velocidad_descarga']))
                                {{ $contenido['velocidad_descarga'] }} {{ $contenido['velocidad_descarga_unidad'] ?? 'Mbps' }}
                            @else
                                —
                            @endif
                        </span>
                    </div>
                </td>
                <td style="width: {{ $tipoConn === 'WIFI' ? '25%' : '33%' }};">
                    <div class="field-box">
                        <span class="field-label">Velocidad Subida</span>
                        <span class="field-value" style="color: #0284c7;">
                            @if(!empty($contenido['velocidad_subida']))
                                {{ $contenido['velocidad_subida'] }} {{ $contenido['velocidad_subida_unidad'] ?? 'Mbps' }}
                            @else
                                —
                            @endif
                        </span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    @endif

    {{-- ═══ 4. OBSERVACIONES Y EVIDENCIAS ═══ --}}
    <div class="section-card">
        <table class="section-header-table">
            <tr>
                <td style="width: 20px;"><span class="section-badge">{{ $secNum++ }}</span></td>
                <td><span class="section-title-text">OBSERVACIONES Y EVIDENCIAS FOTOGRÁFICAS</span></td>
            </tr>
        </table>

        {{-- OBSERVACIONES --}}
        <div style="margin-bottom: 5px;">
            <span class="field-label" style="margin-bottom: 2px;">Observaciones / Incidencias Detectadas</span>
            <div class="obs-box">
                @if(!empty($contenido['observaciones']))
                    {{ $contenido['observaciones'] }}
                @else
                    <span style="color: #94a3b8; font-style: italic;">Sin observaciones o incidencias registradas en este consultorio.</span>
                @endif
            </div>
        </div>

        {{-- EVIDENCIA FOTOGRÁFICA --}}
        @php
            $evidenciaPath = $detalle->contenido['evidencia_path'] ?? $contenido['evidencia_path'] ?? '';
            $fotoBase64 = null;
            if (!empty($evidenciaPath)) {
                $p = storage_path('app/public/' . $evidenciaPath);
                if (file_exists($p)) {
                    $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                    $mime = ($ext === 'png') ? 'image/png' : 'image/jpeg';
                    $fotoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($p));
                }
            }
        @endphp

        <div style="margin-top: 4px;">
            <span class="field-label" style="margin-bottom: 2px;">Fotografía / Evidencia Adjunta</span>
            @if(!empty($fotoBase64))
                <div class="evidence-card">
                    <img src="{{ $fotoBase64 }}" class="evidence-img">
                    <div class="evidence-caption">
                        FOTO 1
                    </div>
                </div>
            @else
                <div style="background-color: #fafbff; border: 1.5px dashed #cbd5e1; border-radius: 5px; padding: 7px; text-align: center; color: #94a3b8; font-size: 7.5px; font-weight: bold; text-transform: uppercase;">
                    Sin evidencia fotográfica adjunta para este consultorio.
                </div>
            @endif
        </div>
    </div>

    {{-- ═══ FOOTER FIJO INSTITUCIONAL ═══ --}}
    <div class="footer-fixed">
        <div class="footer-inner">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="text-align: left; vertical-align: middle;">
                        <span class="footer-text">
                            Sistema de Monitoreo y Evaluación &bull; EESS: {{ strtoupper($acta->establecimiento->nombre ?? '') }} &bull; Consultorio: {{ strtoupper($contenido['titulo_consultorio'] ?? 'CONSULTORIO') }} &bull; Acta #{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }} &bull; {{ date('d/m/Y') }}
                        </span>
                    </td>
                    <td style="text-align: right; width: 40px; vertical-align: middle;">
                        {{-- Espacio reservado para el paginador impreso por script PHP --}}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ═══ SCRIPT DOMPDF: PAGINADOR DINÁMICO (EJ: 1/2, 2/2) ═══ --}}
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("helvetica", "bold");
            $size = 7.5;
            $color = array(0.58, 0.64, 0.72); // #94a3b8
            
            // Altura exacta en el pie de página
            $y = $pdf->get_height() - 24;
            
            // Paginador en la esquina inferior derecha en formato exacto: 1/2, 2/2
            $textPag = "{PAGE_NUM}/{PAGE_COUNT}";
            $anchoPag = $fontMetrics->get_text_width("88/88", $font, $size);
            $xRight = $pdf->get_width() - 28 - $anchoPag;
            
            $pdf->page_text($xRight, $y, $textPag, $font, $size, $color);
        }
    </script>

</body>
</html>
