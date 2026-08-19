<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Infraestructura y Croquis 2D - Acta #{{ $acta->numero_acta }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap');

        @page {
            margin: 1.2cm 1.2cm 1.4cm 1.2cm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', 'Segoe UI', 'Century Gothic', 'Calibri', 'Helvetica Neue', 'Arial', sans-serif;
            font-size: 8px;
            color: #1e293b;
            line-height: 1.45;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* Utilidades Generales */
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .text-left { text-align: left !important; }
        .font-bold { font-weight: 700 !important; }
        .font-black { font-weight: 800 !important; }
        .uppercase { text-transform: uppercase !important; }
        .no-break { page-break-inside: avoid; }

        /* ENCABEZADO INSTITUCIONAL */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border-bottom: 2px solid #0f2b5c;
            padding-bottom: 8px;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .inst-title {
            font-size: 7px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .main-title {
            font-size: 13.5px;
            font-weight: 800;
            color: #0f2b5c;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            margin: 2px 0 3px 0;
        }
        .submodule-badge {
            display: inline-block;
            background-color: #0f2b5c;
            color: #ffffff;
            font-size: 8px;
            font-weight: 700;
            padding: 2.5px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .eess-info {
            font-size: 8px;
            font-weight: 700;
            color: #475569;
            margin-top: 4px;
            text-transform: uppercase;
        }
        .acta-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 6px 12px;
            text-align: center;
        }
        .acta-box-num {
            font-size: 13px;
            font-weight: 800;
            color: #0f2b5c;
            letter-spacing: 0.5px;
        }
        .acta-box-lbl {
            font-size: 7px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        /* SECCIONES Y TARJETAS */
        .card-section {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            margin-bottom: 11px;
            background-color: #ffffff;
            page-break-inside: avoid;
        }
        .card-header {
            background-color: #0f2b5c;
            color: #ffffff;
            padding: 4.5px 9px;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }
        .card-header .num-pill {
            background-color: #2563eb;
            color: #ffffff;
            padding: 1px 5px;
            border-radius: 3px;
            margin-right: 4px;
            font-size: 7.5px;
            font-weight: 800;
        }
        .card-body {
            padding: 0;
        }

        /* RESUMEN MÉTRICAS */
        .metrics-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 11px;
        }
        .metrics-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 4px;
            text-align: center;
            background-color: #f8fafc;
            width: 16.66%;
        }
        .metric-val {
            font-size: 12px;
            font-weight: 800;
            color: #0f2b5c;
            line-height: 1;
        }
        .metric-lbl {
            font-size: 6.5px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            margin-top: 3px;
            letter-spacing: 0.3px;
        }

        /* TABLAS DE DATOS */
        table.grid-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        table.grid-table td, table.grid-table th {
            border: 1px solid #e2e8f0;
            padding: 4.5px 7px;
            font-size: 7.5px;
            vertical-align: middle;
        }
        table.grid-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 7px;
            letter-spacing: 0.4px;
        }
        .lbl-col {
            background-color: #f8fafc;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            font-size: 7.5px;
        }
        .val-col {
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
        }

        /* TABLA ELEGANTE DE LISTADO */
        table.list-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        table.list-table th {
            background-color: #0f2b5c;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 7px;
            letter-spacing: 0.3px;
            padding: 4.5px 6px;
            border: 1px solid #0f2b5c;
            text-align: left;
        }
        table.list-table td {
            border: 1px solid #e2e8f0;
            padding: 4.5px 6px;
            font-size: 7.5px;
            vertical-align: middle;
        }
        table.list-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        table.list-table tr.subtotal td {
            background-color: #eff6ff;
            font-weight: 800;
            color: #1e3a8a;
            border-top: 1.5px solid #bfdbfe;
        }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 1.5px 6px;
            border-radius: 3px;
            font-size: 6.5px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
        }
        .badge-op { background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-re { background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .badge-in { background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-info { background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-neutral { background-color: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }

        /* CROQUIS CONTAINER */
        .croquis-wrapper {
            padding: 10px;
            text-align: center;
            background-color: #f8fafc;
        }
        .croquis-img {
            max-width: 100%;
            max-height: 280px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            padding: 4px;
            background-color: #ffffff;
        }
        .vacio-box {
            padding: 14px;
            text-align: center;
            color: #94a3b8;
            font-style: italic;
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
        }

        /* SECCIÓN DE FIRMAS */
        .signatures-container {
            width: 100%;
            margin-top: 14px;
            page-break-inside: avoid;
        }
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signatures-table td {
            width: 50%;
            padding: 0 10px;
            border: none;
            vertical-align: top;
        }
        .sig-card {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background-color: #ffffff;
            text-align: center;
            padding-bottom: 7px;
        }
        .sig-space {
            height: 44px;
        }
        .sig-line {
            width: 80%;
            border-top: 1px solid #475569;
            margin: 0 auto 5px auto;
        }
        .sig-name {
            font-size: 7.5px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .sig-role {
            font-size: 6.5px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            margin-top: 1px;
            letter-spacing: 0.3px;
        }
    </style>
</head>
<body>

    {{-- ENCABEZADO INSTITUCIONAL --}}
    <table class="header-table">
        <tr>
            <td style="width: 72%;">
                <div class="inst-title">MINISTERIO DE SALUD &bull; DIRECCI&Oacute;N DE REDES INTEGRADAS DE SALUD</div>
                <div class="main-title">REPORTE DE INFRAESTRUCTURA Y CROQUIS 2D</div>
                <div>
                    <span class="submodule-badge">M&Oacute;DULO FIJO &bull; INFRAESTRUCTURA 2D</span>
                </div>
                <div class="eess-info">
                    <strong>EESS:</strong> {{ $acta->establecimiento->codigo ?? 'S/C' }} - {{ strtoupper($acta->establecimiento->nombre) }} &nbsp;|&nbsp; 
                    <strong>PROVINCIA:</strong> {{ strtoupper($acta->establecimiento->provincia ?? 'GENERAL') }}
                </div>
            </td>
            <td style="width: 28%;" class="text-right">
                <div class="acta-box">
                    <div class="acta-box-lbl">DIAGNOSTICO SITUACIONAL</div>
                    <div class="acta-box-num">N&deg; {{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}</div>
                    <div style="font-size: 7px; font-weight: 700; color: #475569; margin-top: 2px;">
                        FECHA: {{ $acta->fecha ? date('d/m/Y', strtotime($acta->fecha)) : date('d/m/Y') }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- DATOS DEL ESTABLECIMIENTO --}}
    <div class="card-section">
        <div class="card-header">
            DATOS DEL ESTABLECIMIENTO Y RED DE SALUD
        </div>
        <div class="card-body">
            <table class="grid-table">
                <tr>
                    <td class="lbl-col" style="width: 20%;">C&Oacute;DIGO EESS:</td>
                    <td class="val-col" style="width: 30%;">{{ $acta->establecimiento->codigo ?? 'S/C' }}</td>
                    <td class="lbl-col" style="width: 20%;">RED DE SALUD:</td>
                    <td class="val-col" style="width: 30%;">{{ strtoupper($acta->establecimiento->red ?? 'GENERAL') }}</td>
                </tr>
                <tr>
                    <td class="lbl-col">PROVINCIA:</td>
                    <td class="val-col">{{ strtoupper($acta->establecimiento->provincia ?? 'GENERAL') }}</td>
                    <td class="lbl-col">DISTRITO:</td>
                    <td class="val-col">{{ strtoupper($acta->establecimiento->distrito ?? 'GENERAL') }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- RESUMEN MÉTRICAS --}}
    <table class="metrics-table">
        <tr>
            <td>
                <div class="metric-val">{{ $resumen['ambientes'] ?? 0 }}</div>
                <div class="metric-lbl">Ambientes</div>
            </td>
            <td>
                <div class="metric-val">{{ $resumen['unidades'] ?? 0 }}</div>
                <div class="metric-lbl">Total Equipos</div>
            </td>
            <td>
                <div class="metric-val" style="color: #047857;">{{ $resumen['OPERATIVO'] ?? 0 }}</div>
                <div class="metric-lbl" style="color: #047857;">Operativos</div>
            </td>
            <td>
                <div class="metric-val" style="color: #b45309;">{{ $resumen['REGULAR'] ?? 0 }}</div>
                <div class="metric-lbl" style="color: #b45309;">Regulares</div>
            </td>
            <td>
                <div class="metric-val" style="color: #b91c1c;">{{ $resumen['INOPERATIVO'] ?? 0 }}</div>
                <div class="metric-lbl" style="color: #b91c1c;">Inoperativos</div>
            </td>
            <td>
                <div class="metric-val">{{ $resumen['pisos'] ?? 1 }}</div>
                <div class="metric-lbl">Pisos / Niveles</div>
            </td>
        </tr>
    </table>

    {{-- CROQUIS 2D --}}
    @php
        $imagen_path = $contenido['imagen_path'] ?? null;
        $base64 = null;
        if ($imagen_path) {
            $path = storage_path('app/public/' . $imagen_path);
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($path));
            }
        }
    @endphp

    <div class="card-section">
        <div class="card-header">
            PLANO Y CROQUIS 2D DEL ESTABLECIMIENTO
        </div>
        <div class="croquis-wrapper">
            @if($base64)
                <img src="{{ $base64 }}" class="croquis-img">
            @else
                <div class="vacio-box">
                    No se ha exportado una imagen de croquis 2D para este establecimiento.
                </div>
            @endif
        </div>
    </div>

    {{-- CUADRO 1: AMBIENTES --}}
    <div class="card-section">
        <div class="card-header">
            <span class="num-pill">1</span> AMBIENTES Y SERVICIOS REGISTRADOS
        </div>
        <div class="card-body">
            @if(count($ambientes ?? []))
                <table class="list-table">
                    <thead>
                        <tr>
                            <th style="width: 4%;" class="text-center">N&deg;</th>
                            <th style="width: 31%;">Ambiente</th>
                            <th style="width: 19%;">Tipo</th>
                            <th style="width: 7%;" class="text-center">Piso</th>
                            <th style="width: 9%;" class="text-center">Wifi</th>
                            <th style="width: 9%;" class="text-center">Energ&iacute;a</th>
                            <th style="width: 10%;" class="text-center">Puntos Red</th>
                            <th style="width: 11%;" class="text-center">Equipos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ambientes as $i => $amb)
                            <tr>
                                <td class="text-center font-bold">{{ $i + 1 }}</td>
                                <td class="font-bold uppercase">{{ $amb['nombre'] }}</td>
                                <td class="uppercase">{{ $amb['tipo'] }}</td>
                                <td class="text-center">{{ $amb['piso'] }}</td>
                                <td class="text-center">
                                    @if($amb['wifi'])
                                        <span class="badge badge-op">S&Iacute;</span>
                                    @else
                                        <span class="badge badge-neutral">&mdash;</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($amb['luz'])
                                        <span class="badge badge-op">S&Iacute;</span>
                                    @else
                                        <span class="badge badge-neutral">&mdash;</span>
                                    @endif
                                </td>
                                <td class="text-center font-bold">{{ $amb['red'] ?: '&mdash;' }}</td>
                                <td class="text-center font-bold" style="color: #0f2b5c;">{{ $amb['unidades'] ?: '&mdash;' }}</td>
                            </tr>
                        @endforeach
                        <tr class="subtotal">
                            <td colspan="7">TOTAL DE AMBIENTES EVALUADOS: {{ count($ambientes) }}</td>
                            <td class="text-center">{{ array_sum(array_column($ambientes, 'unidades')) }}</td>
                        </tr>
                    </tbody>
                </table>
            @else
                <div class="vacio-box">
                    No se registraron ambientes en el croquis 2D.
                </div>
            @endif
        </div>
    </div>

    {{-- CUADRO 2: EQUIPAMIENTO --}}
    <div class="card-section">
        <div class="card-header">
            <span class="num-pill">2</span> EQUIPAMIENTO INFORM&Aacute;TICO Y SU UBICACI&Oacute;N
        </div>
        <div class="card-body">
            @if(count($equipos ?? []))
                <table class="list-table">
                    <thead>
                        <tr>
                            <th style="width: 4%;" class="text-center">N&deg;</th>
                            <th style="width: 25%;">Equipo</th>
                            <th style="width: 7%;" class="text-center">Cant.</th>
                            <th style="width: 15%;" class="text-center">Estado</th>
                            <th style="width: 41%;">Ubicaci&oacute;n (Ambiente Asignado)</th>
                            <th style="width: 8%;" class="text-center">Piso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($equipos as $i => $eq)
                            @php
                                $clase = $eq['estado'] === 'OPERATIVO' ? 'badge-op' : ($eq['estado'] === 'REGULAR' ? 'badge-re' : ($eq['estado'] === 'INOPERATIVO' ? 'badge-in' : 'badge-neutral'));
                            @endphp
                            <tr>
                                <td class="text-center font-bold">{{ $i + 1 }}</td>
                                <td class="font-bold uppercase">{{ $eq['equipo'] }}</td>
                                <td class="text-center font-bold">{{ $eq['cantidad'] }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $clase }}">{{ $eq['estado'] }}</span>
                                </td>
                                <td class="uppercase">{{ $eq['ubicacion'] }}</td>
                                <td class="text-center font-bold">{{ $eq['piso'] }}</td>
                            </tr>
                        @endforeach
                        <tr class="subtotal">
                            <td colspan="2">TOTAL DE EQUIPOS EN CROQUIS:</td>
                            <td class="text-center">{{ $resumen['unidades'] ?? 0 }}</td>
                            <td colspan="3" style="font-size: 7px;">
                                Operativos: {{ $resumen['OPERATIVO'] ?? 0 }} &bull;
                                Regulares: {{ $resumen['REGULAR'] ?? 0 }} &bull;
                                Inoperativos: {{ $resumen['INOPERATIVO'] ?? 0 }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            @else
                <div class="vacio-box">
                    No se registraron equipos inform&aacute;ticos en el croquis 2D.
                </div>
            @endif
        </div>
    </div>

    {{-- CUADRO 3: SISTEMAS DE INFORMACIÓN --}}
    @if(count($sistemas ?? []))
    <div class="card-section">
        <div class="card-header">
            <span class="num-pill">3</span> SISTEMAS DE INFORMACI&Oacute;N Y UBICACI&Oacute;N
        </div>
        <div class="card-body">
            <table class="list-table">
                <thead>
                    <tr>
                        <th style="width: 4%;" class="text-center">N&deg;</th>
                        <th style="width: 35%;">Sistema de Informaci&oacute;n</th>
                        <th style="width: 53%;">Ambiente Donde Se Utiliza</th>
                        <th style="width: 8%;" class="text-center">Piso</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sistemas as $i => $sis)
                        <tr>
                            <td class="text-center font-bold">{{ $i + 1 }}</td>
                            <td class="font-bold uppercase">{{ $sis['sistema'] }}</td>
                            <td class="uppercase">{{ $sis['ubicacion'] }}</td>
                            <td class="text-center font-bold">{{ $sis['piso'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- CUADRO 4: ACCESOS Y VÍAS --}}
    @if(count($accesos ?? []))
    <div class="card-section">
        <div class="card-header">
            <span class="num-pill">{{ count($sistemas ?? []) ? '4' : '3' }}</span> ACCESOS Y V&Iacute;AS DE CIRCULACI&Oacute;N
        </div>
        <div class="card-body">
            <table class="list-table">
                <thead>
                    <tr>
                        <th style="width: 4%;" class="text-center">N&deg;</th>
                        <th style="width: 43%;">Elemento de Acceso</th>
                        <th style="width: 45%;">Denominaci&oacute;n / Ubicaci&oacute;n</th>
                        <th style="width: 8%;" class="text-center">Piso</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($accesos as $i => $ac)
                        <tr>
                            <td class="text-center font-bold">{{ $i + 1 }}</td>
                            <td class="font-bold uppercase">{{ $ac['elemento'] }}</td>
                            <td class="uppercase">{{ $ac['nombre'] }}</td>
                            <td class="text-center font-bold">{{ $ac['piso'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- FIRMAS DE CONFORMIDAD --}}
    <div class="signatures-container">
        <table class="signatures-table">
            <tr>
                <td>
                    <div class="sig-card">
                        <div class="sig-space"></div>
                        <div class="sig-line"></div>
                        <div class="sig-name">{{ strtoupper($acta->responsable ?? 'RESPONSABLE DEL ESTABLECIMIENTO / JEFE') }}</div>
                        <div class="sig-role">JEFE / RESPONSABLE DEL EESS</div>
                    </div>
                </td>
                <td>
                    <div class="sig-card">
                        <div class="sig-space"></div>
                        <div class="sig-line"></div>
                        <div class="sig-name">{{ strtoupper($monitor['nombre'] ?? 'MONITOR TI') }}</div>
                        <div class="sig-role">MONITOR / IMPLEMENTADOR TI</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
