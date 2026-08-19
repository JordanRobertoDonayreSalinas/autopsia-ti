<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Padrón de Recursos Humanos por Servicio - Acta #{{ $acta->numero_acta }}</title>
    <style>
        /* ═══════════════════════════════════════════════════════
           PADRÓN RR.HH — DISEÑO PREMIUM LANDSCAPE A4
           Fuente: DejaVu Sans (nativa DomPDF, nítida, con tildes)
           ═══════════════════════════════════════════════════════ */
        @page { margin: 0.8cm 1cm 1.6cm 1cm; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8.5px;
            color: #1e293b;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        /* ── BARRA SUPERIOR DECORATIVA ── */
        .top-accent {
            height: 4px;
            background-color: #4f46e5;
            margin-bottom: 10px;
        }

        /* ── ENCABEZADO INSTITUCIONAL ── */
        .header-block {
            margin-bottom: 10px;
        }
        .header-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .header-grid td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        /* Lado izquierdo del header */
        .header-badge {
            background-color: #4f46e5;
            color: #ffffff;
            font-size: 7px;
            font-weight: bold;
            padding: 3px 10px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            display: inline-block;
        }
        .header-acta-num {
            font-size: 8px;
            color: #94a3b8;
            font-weight: bold;
            margin-left: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .header-title {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: -0.3px;
            margin: 5px 0 3px 0;
        }
        .header-subtitle {
            font-size: 8.5px;
            color: #475569;
        }
        .header-subtitle strong {
            color: #1e293b;
        }

        /* Lado derecho: tarjetas de resumen */
        .summary-cards {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px 0;
        }
        .summary-cards td {
            border: none;
            padding: 0;
        }
        .stat-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 8px;
            text-align: center;
        }
        .stat-card-accent {
            background-color: #eef2ff;
            border: 1px solid #c7d2fe;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #4f46e5;
            display: block;
            line-height: 1.1;
        }
        .stat-label {
            font-size: 6.5px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-top: 1px;
        }

        /* ── LÍNEA SEPARADORA POST-HEADER ── */
        .header-divider {
            border: none;
            height: 1.5px;
            background-color: #e2e8f0;
            margin: 8px 0 10px 0;
        }

        /* ── TABLA PRINCIPAL DE TRABAJADORES ── */
        table.rrhh-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.rrhh-table thead tr {
            background-color: #4f46e5;
        }
        table.rrhh-table th {
            color: #ffffff;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 7px 6px;
            text-align: left;
            border: none;
            letter-spacing: 0.4px;
        }
        table.rrhh-table th:first-child {
            border-radius: 4px 0 0 0;
        }
        table.rrhh-table th:last-child {
            border-radius: 0 4px 0 0;
        }
        table.rrhh-table td {
            border-bottom: 1px solid #f1f5f9;
            padding: 6px 6px;
            font-size: 8px;
            vertical-align: middle;
        }
        table.rrhh-table tbody tr:nth-child(even) {
            background-color: #fafbff;
        }
        table.rrhh-table tbody tr:last-child td {
            border-bottom: 2px solid #e2e8f0;
        }

        /* Numeración de fila */
        .row-num {
            font-weight: bold;
            color: #94a3b8;
            text-align: center;
            font-size: 8px;
        }

        /* ── BADGES / PÍLDORAS ── */
        .pill {
            display: inline-block;
            padding: 2.5px 7px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .pill-servicio {
            background-color: #eef2ff;
            color: #4338ca;
            border: 1px solid #c7d2fe;
        }
        .pill-si {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }
        .pill-no {
            background-color: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        /* Celda nombre principal */
        .nombre-completo {
            font-weight: bold;
            color: #0f172a;
            font-size: 8.5px;
            text-transform: uppercase;
        }

        /* Dato tipo documento */
        .tipo-doc-label {
            font-size: 6.5px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: block;
            margin-bottom: 1px;
        }
        .doc-value {
            font-weight: bold;
            color: #0f172a;
            font-size: 9px;
            letter-spacing: 0.3px;
        }

        /* Profesión */
        .profesion-text {
            font-size: 7.5px;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
        }

        /* Colegiatura */
        .colegiatura-text {
            font-size: 8px;
            font-weight: bold;
            color: #1e293b;
        }
        .rne-text {
            font-size: 7px;
            color: #4f46e5;
            font-weight: bold;
            margin-top: 1px;
        }
        .sin-dato {
            color: #cbd5e1;
            font-size: 7px;
            font-style: italic;
        }

        /* Contacto */
        .contacto-tel {
            font-size: 8px;
            color: #1e293b;
            font-weight: bold;
        }
        .contacto-email {
            font-size: 6.5px;
            color: #64748b;
            margin-top: 1px;
            word-break: break-all;
        }

        /* Periodo SERUMS */
        .periodo-valor {
            font-weight: bold;
            color: #4338ca;
            font-size: 8.5px;
        }

        /* ── SECCIÓN OBSERVACIONES ── */
        .obs-section {
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .obs-container {
            background-color: #fafbff;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #4f46e5;
            border-radius: 0 6px 6px 0;
            padding: 8px 12px;
        }
        .obs-label {
            font-size: 7.5px;
            font-weight: bold;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .obs-text {
            font-size: 8.5px;
            color: #334155;
            line-height: 1.4;
        }

        /* ── EVIDENCIA FOTOGRÁFICA ── */
        .evidencia-section {
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .evidencia-title {
            font-size: 7.5px;
            font-weight: bold;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .foto-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
        }
        .foto-grid td {
            border: none;
            padding: 0;
            vertical-align: top;
            text-align: center;
        }
        .foto-frame {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px;
            background-color: #fafbff;
        }
        .foto-frame img {
            max-height: 170px;
            max-width: 100%;
            border-radius: 4px;
        }
        .foto-caption {
            font-size: 7px;
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 4px;
            letter-spacing: 0.3px;
        }

        /* ── MENSAJE VACÍO (TABLA) ── */
        .empty-row td {
            text-align: center;
            padding: 24px;
            color: #94a3b8;
            font-weight: bold;
            font-size: 9px;
            background-color: #fafbff;
            border-bottom: 2px solid #e2e8f0;
        }

        /* ── FOOTER FIJO ── */
        .footer-fixed {
            position: fixed;
            bottom: -1cm;
            left: 0;
            right: 0;
            text-align: center;
        }
        .footer-inner {
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
        .footer-text {
            font-size: 7px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

    {{-- BARRA DECORATIVA SUPERIOR --}}
    <div class="top-accent"></div>

    {{-- ═══ ENCABEZADO INSTITUCIONAL ═══ --}}
    <div class="header-block">
        <table class="header-grid">
            <tr>
                {{-- COLUMNA IZQUIERDA: Info del reporte --}}
                <td style="width: 68%;">
                    <div>
                        <span class="header-badge">Padrón de RR.HH</span>
                        <span class="header-acta-num">Acta N° {{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="header-title">Padrón de Recursos Humanos por Servicio</div>
                    <div class="header-subtitle">
                        <strong>IPRESS:</strong> {{ $acta->establecimiento->codigo ?? 'S/C' }} — {{ strtoupper($acta->establecimiento->nombre ?? 'NO REGISTRADO') }}
                        &nbsp;&bull;&nbsp;
                        <strong>Red:</strong> {{ strtoupper($acta->establecimiento->red ?? 'No especificada') }}
                        @if(!empty($acta->establecimiento->microred))
                            &nbsp;&bull;&nbsp; <strong>Microred:</strong> {{ strtoupper($acta->establecimiento->microred) }}
                        @endif
                    </div>
                </td>

                {{-- COLUMNA DERECHA: Tarjetas de resumen --}}
                <td style="width: 32%;">
                    <table class="summary-cards">
                        <tr>
                            <td style="width: 50%;">
                                <div class="stat-card stat-card-accent">
                                    <span class="stat-value">{{ count($trabajadores) }}</span>
                                    <span class="stat-label">Trabajadores</span>
                                </div>
                            </td>
                            <td style="width: 50%;">
                                <div class="stat-card">
                                    <span class="stat-value" style="color: #334155;">{{ date('d/m') }}</span>
                                    <span class="stat-label">Fecha Reporte</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <hr class="header-divider">

    {{-- ═══ TABLA PRINCIPAL DE PERSONAL ═══ --}}
    <table class="rrhh-table">
        <thead>
            <tr>
                <th style="width: 22px; text-align: center;">#</th>
                <th style="width: 80px;">Servicio</th>
                <th style="width: 75px;">Documento</th>
                <th>Apellidos y Nombres</th>
                <th style="width: 120px;">Profesión</th>
                <th style="width: 90px;">Colegiatura / RNE</th>
                <th style="width: 115px;">Contacto</th>
                <th style="width: 52px; text-align: center;">SERUMS</th>
                <th style="width: 52px; text-align: center;">Periodo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trabajadores as $index => $t)
                <tr>
                    {{-- # --}}
                    <td class="row-num">{{ $index + 1 }}</td>

                    {{-- SERVICIO --}}
                    <td>
                        <span class="pill pill-servicio">{{ $t['servicio'] ?? 'MEDICINA' }}</span>
                    </td>

                    {{-- DOCUMENTO --}}
                    <td>
                        <span class="tipo-doc-label">{{ $t['tipo_doc'] ?? 'DNI' }}</span>
                        <span class="doc-value">{{ $t['doc'] ?? '—' }}</span>
                    </td>

                    {{-- NOMBRE COMPLETO --}}
                    <td>
                        <span class="nombre-completo">
                            {{ $t['apellido_paterno'] ?? '' }} {{ $t['apellido_materno'] ?? '' }}, {{ $t['nombres'] ?? '' }}
                        </span>
                    </td>

                    {{-- PROFESIÓN --}}
                    <td>
                        <span class="profesion-text">{{ $t['profesion'] ?? 'NO ESPECIFICADO' }}</span>
                    </td>

                    {{-- COLEGIATURA / RNE --}}
                    <td>
                        @if(!empty($t['colegiatura']))
                            <div class="colegiatura-text">
                                {{ !empty($t['colegio_profesional']) ? $t['colegio_profesional'] . ' ' : '' }}{{ $t['colegiatura'] }}
                            </div>
                        @endif
                        @if(!empty($t['rne']))
                            <div class="rne-text">RNE: {{ $t['rne'] }}</div>
                        @endif
                        @if(empty($t['colegiatura']) && empty($t['rne']))
                            <span class="sin-dato">S/N</span>
                        @endif
                    </td>

                    {{-- CONTACTO --}}
                    <td>
                        @if(!empty($t['celular']))
                            <div class="contacto-tel">{{ $t['celular'] }}</div>
                        @endif
                        @if(!empty($t['correo']))
                            <div class="contacto-email">{{ $t['correo'] }}</div>
                        @endif
                        @if(empty($t['celular']) && empty($t['correo']))
                            <span class="sin-dato">Sin contacto</span>
                        @endif
                    </td>

                    {{-- SERUMS --}}
                    <td style="text-align: center;">
                        @if(($t['es_serums'] ?? '') === 'SI')
                            <span class="pill pill-si">Sí</span>
                        @else
                            <span class="pill pill-no">No</span>
                        @endif
                    </td>

                    {{-- PERIODO --}}
                    <td style="text-align: center;">
                        @if(($t['es_serums'] ?? '') === 'SI')
                            <span class="periodo-valor">{{ $t['periodo_serums'] ?? 'S/P' }}</span>
                        @else
                            <span style="color: #cbd5e1;">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="9">
                        No se registraron trabajadores en el padrón de recursos humanos
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ═══ OBSERVACIONES ═══ --}}
    @if(!empty($contenido['observaciones']))
        <div class="obs-section">
            <div class="obs-container">
                <div class="obs-label">Observaciones</div>
                <div class="obs-text">{{ $contenido['observaciones'] }}</div>
            </div>
        </div>
    @endif

    {{-- ═══ EVIDENCIA FOTOGRÁFICA ═══ --}}
    @php
        $foto1Base64 = null;
        if (!empty($contenido['foto_1'])) {
            $p1 = storage_path('app/public/' . $contenido['foto_1']);
            if (file_exists($p1)) {
                $foto1Base64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($p1));
            }
        }
        $foto2Base64 = null;
        if (!empty($contenido['foto_2'])) {
            $p2 = storage_path('app/public/' . $contenido['foto_2']);
            if (file_exists($p2)) {
                $foto2Base64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($p2));
            }
        }
    @endphp

    @if($foto1Base64 || $foto2Base64)
        <div class="evidencia-section">
            <div class="evidencia-title">Evidencia Fotográfica</div>
            <table class="foto-grid">
                <tr>
                    @if($foto1Base64)
                        <td style="width: {{ $foto2Base64 ? '50%' : '100%' }};">
                            <div class="foto-frame">
                                <img src="{{ $foto1Base64 }}">
                                <div class="foto-caption">Foto 1</div>
                            </div>
                        </td>
                    @endif
                    @if($foto2Base64)
                        <td style="width: {{ $foto1Base64 ? '50%' : '100%' }};">
                            <div class="foto-frame">
                                <img src="{{ $foto2Base64 }}">
                                <div class="foto-caption">Foto 2</div>
                            </div>
                        </td>
                    @endif
                </tr>
            </table>
        </div>
    @endif

    {{-- ═══ FOOTER FIJO INSTITUCIONAL ═══ --}}
    <div class="footer-fixed">
        <div class="footer-inner">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="text-align: left; vertical-align: middle;">
                        <span class="footer-text">
                            Sistema de Monitoreo y Evaluación &bull; Reporte de Recursos Humanos &bull; Acta #{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }} &bull; {{ date('d/m/Y') }}
                        </span>
                    </td>
                    <td style="text-align: right; width: 40px; vertical-align: middle;">
                        {{-- Espacio reservado para el paginador --}}
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
            $y = $pdf->get_height() - 25;
            
            // Paginador en la esquina inferior derecha en formato exacto: 1/2, 2/2
            $textPag = "{PAGE_NUM}/{PAGE_COUNT}";
            $anchoPag = $fontMetrics->get_text_width("88/88", $font, $size);
            $xRight = $pdf->get_width() - 28 - $anchoPag;
            
            $pdf->page_text($xRight, $y, $textPag, $font, $size, $color);
        }
    </script>

</body>
</html>
