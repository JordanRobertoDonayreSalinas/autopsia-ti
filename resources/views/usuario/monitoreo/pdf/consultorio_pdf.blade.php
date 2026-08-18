<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Evaluación de Consultorio: {{ $contenido['titulo_consultorio'] ?? 'Consultorio' }} - Acta {{ $acta->numero_acta }}</title>
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
            border-bottom: 2px solid #1e3a8a;
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
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            margin: 2px 0 3px 0;
        }
        .submodule-badge {
            display: inline-block;
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 8.5px;
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
            letter-spacing: 0.2px;
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
            color: #1e3a8a;
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
            background-color: #1e293b;
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
            background-color: #3b82f6;
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

        /* TABLAS DE DATOS */
        table.grid-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        table.grid-table td, table.grid-table th {
            border: 1px solid #e2e8f0;
            padding: 5px 8px;
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
            letter-spacing: 0.2px;
        }
        .val-col {
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
        }

        /* BADGES DE ESTADO */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: center;
        }
        .badge-success { background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-danger  { background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-warning { background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .badge-info    { background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-neutral { background-color: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }

        /* OBSERVACIONES */
        .obs-box {
            background-color: #f8fafc;
            padding: 8px 10px;
            font-size: 7.5px;
            color: #0f172a;
            font-weight: 600;
            line-height: 1.45;
        }

        /* EVIDENCIA FOTOGRÁFICA */
        .photo-wrapper {
            padding: 9px;
            text-align: center;
            background-color: #f8fafc;
        }
        .photo-img {
            max-width: 95%;
            max-height: 220px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            padding: 2px;
            background-color: #ffffff;
        }
        .photo-meta {
            margin-top: 5px;
            font-size: 7px;
            font-weight: 700;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .no-photo-box {
            padding: 14px;
            text-align: center;
            color: #94a3b8;
            font-style: italic;
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
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

        /* PIE DE PÁGINA */
        #footer {
            position: fixed;
            bottom: -0.9cm;
            left: 0;
            right: 0;
            height: 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
            font-size: 6.5px;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
    </style>
</head>
<body>

    {{-- PIE DE PÁGINA FIJO --}}
    <div id="footer">
        Sistema de Evaluación & Monitoreo TI &bull; EESS: {{ strtoupper($acta->establecimiento->nombre ?? '') }} &bull; Acta #{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }} &bull; Generado: {{ date('d/m/Y H:i:s') }}
    </div>

    @php 
        $contenido = $detalle->contenido ?? [];
        $elec = strtoupper($contenido['cuenta_electricidad'] ?? 'SI');
        $red  = strtoupper($contenido['cuenta_punto_red'] ?? 'SI');
    @endphp

    {{-- ENCABEZADO INSTITUCIONAL --}}
    <table class="header-table">
        <tr>
            <td style="width: 72%;">
                <div class="inst-title">MINISTERIO DE SALUD &bull; DIRECCI&Oacute;N DE REDES INTEGRADAS DE SALUD</div>
                <div class="main-title">EVALUACI&Oacute;N DE DIAGN&Oacute;STICO SITUACIONAL</div>
                <div>
                    <span class="submodule-badge">{{ $contenido['titulo_consultorio'] ?? strtoupper(str_replace('_', ' ', $detalle->modulo_nombre ?? 'CONSULTORIO')) }}</span>
                </div>
                <div class="eess-info">
                    <strong>EESS:</strong> {{ $acta->establecimiento->codigo ?? 'S/C' }} - {{ strtoupper($acta->establecimiento->nombre) }} &nbsp;|&nbsp; 
                    <strong>PROVINCIA:</strong> {{ strtoupper($acta->establecimiento->provincia ?? 'GENERAL') }}
                </div>
            </td>
            <td style="width: 28%;" class="text-right">
                <div class="acta-box">
                    <div class="acta-box-lbl">Diagn&oacute;stico Situacional</div>
                    <div class="acta-box-num">N&deg; {{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}</div>
                    <div style="font-size: 7px; font-weight: 700; color: #475569; margin-top: 2px;">
                        FECHA: {{ isset($contenido['fecha']) ? date('d/m/Y', strtotime($contenido['fecha'])) : ($acta->fecha ? date('d/m/Y', strtotime($acta->fecha)) : date('d/m/Y')) }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- 1. DATOS GENERALES DEL CONSULTORIO --}}
    <div class="card-section">
        <div class="card-header">
            <span class="num-pill">1</span> DATOS GENERALES Y CONDICIONES B&Aacute;SICAS
        </div>
        <div class="card-body">
            <table class="grid-table">
                <tr>
                    <td class="lbl-col" style="width: 22%;">FECHA EVALUADA:</td>
                    <td class="val-col" style="width: 28%;">{{ isset($contenido['fecha']) ? date('d/m/Y', strtotime($contenido['fecha'])) : date('d/m/Y') }}</td>
                    <td class="lbl-col" style="width: 22%;">TURNO EVALUADO:</td>
                    <td class="val-col" style="width: 28%;">
                        <span class="badge badge-info">{{ $contenido['turno'] ?? 'MAÑANA' }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="lbl-col">TIPO DE CONSULTORIO:</td>
                    <td class="val-col">{{ $contenido['tipo_consultorio'] ?? 'FISICO' }}</td>
                    <td class="lbl-col">PISO / UBICACI&Oacute;N:</td>
                    <td class="val-col">{{ is_numeric($contenido['piso'] ?? '') ? ('PISO ' . $contenido['piso']) : ($contenido['piso'] ?? 'PISO 1') }}</td>
                </tr>
                <tr>
                    <td class="lbl-col">FLUJO EL&Eacute;CTRICO:</td>
                    <td class="val-col">
                        @if($elec === 'SI')
                            <span class="badge badge-success">S&Iacute; CUENTA CON ELECTRICIDAD</span>
                        @else
                            <span class="badge badge-danger">NO CUENTA CON ELECTRICIDAD</span>
                        @endif
                    </td>
                    <td class="lbl-col">PUNTO DE RED:</td>
                    <td class="val-col">
                        @if($red === 'SI')
                            <span class="badge badge-success">S&Iacute; CUENTA (HABILITADO)</span>
                        @else
                            <span class="badge badge-danger">NO CUENTA CON PUNTO RED</span>
                        @endif
                    </td>
                </tr>
                @if(!empty($contenido['servicio_asociado']))
                <tr>
                    <td class="lbl-col">SERVICIO ASOCIADO:</td>
                    <td class="val-col" colspan="3">
                        <span class="badge badge-neutral" style="font-size: 7.5px;">{{ strtoupper($contenido['servicio_asociado']) }}</span>
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- 2. EQUIPOS DE CÓMPUTO E IMPRESORAS --}}
    @php
        $equipos = \App\Models\EquipoComputo::where('cabecera_monitoreo_id', $acta->id)
            ->where('modulo', $detalle->modulo_nombre ?? '')
            ->get();
    @endphp

    <div class="card-section">
        <div class="card-header">
            <span class="num-pill">2</span> INVENTARIO DE EQUIPOS DE C&Oacute;MPUTO Y PERIF&Eacute;RICOS
        </div>
        <div class="card-body">
            @if(count($equipos) > 0)
                <table class="grid-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 4%;">N&deg;</th>
                            <th style="width: 28%;">DESCRIPCI&Oacute;N DEL EQUIPO</th>
                            <th class="text-center" style="width: 6%;">CANT.</th>
                            <th class="text-center" style="width: 14%;">ESTADO</th>
                            <th class="text-center" style="width: 14%;">PROPIEDAD</th>
                            <th style="width: 16%;">N&deg; SERIE / C&Oacute;D. PAT.</th>
                            <th style="width: 18%;">OBSERVACIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($equipos as $idx => $eq)
                            @php
                                $estado = strtoupper($eq->estado ?? 'OPERATIVO');
                                $badgeClass = 'badge-success';
                                if($estado === 'REGULAR') $badgeClass = 'badge-warning';
                                if($estado === 'INOPERATIVO') $badgeClass = 'badge-danger';
                            @endphp
                            <tr>
                                <td class="text-center font-bold">{{ $idx + 1 }}</td>
                                <td class="font-bold uppercase">{{ $eq->descripcion }}</td>
                                <td class="text-center font-bold">{{ $eq->cantidad ?? 1 }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $badgeClass }}">{{ $estado }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ strtoupper($eq->propio ?? 'EXCLUSIVO') }}</span>
                                </td>
                                <td class="font-bold uppercase" style="color: #1e3a8a;">{{ $eq->nro_serie ?: 'S/N' }}</td>
                                <td class="uppercase" style="color: #64748b; font-size: 7px;">{{ $eq->observacion ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-photo-box">
                    No se registraron equipos de c&oacute;mputo en este consultorio.
                </div>
            @endif
        </div>
    </div>

    {{-- 3. CONECTIVIDAD Y SERVICIOS TI --}}
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
        $tipoConn = strtoupper($contenido['tipo_conectividad'] ?? 'CABLEADO');
        $lector   = strtoupper($contenido['lector_dnie'] ?? 'OPERATIVO');
    @endphp

    @if($hasComputoPdf)
    <div class="card-section">
        <div class="card-header">
            <span class="num-pill">3</span> CONECTIVIDAD Y LECTORES DIGITALES
        </div>
        <div class="card-body">
            <table class="grid-table">
                <tr>
                    <td class="lbl-col" style="width: 22%;">TIPO DE CONECTIVIDAD:</td>
                    <td class="val-col" style="width: 28%;">
                        @if($tipoConn === 'CABLEADO')
                            <span class="badge badge-info">CABLEADO (ETHERNET)</span>
                        @elseif($tipoConn === 'WIFI')
                            <span class="badge badge-info">WIFI (INAL&Aacute;MBRICO)</span>
                        @else
                            <span class="badge badge-danger">{{ $tipoConn }}</span>
                        @endif
                    </td>
                    <td class="lbl-col" style="width: 22%;">OPERADOR DE INTERNET:</td>
                    <td class="val-col" style="width: 28%;">
                        {{ strtoupper($contenido['operador_servicio'] ?? 'NO REGISTRADO') }}
                    </td>
                </tr>
                <tr>
                    <td class="lbl-col">LECTOR DE DNIe:</td>
                    <td class="val-col">
                        @if($lector === 'OPERATIVO')
                            <span class="badge badge-success">OPERATIVO</span>
                        @elseif($lector === 'REGULAR')
                            <span class="badge badge-warning">REGULAR</span>
                        @elseif($lector === 'INOPERATIVO')
                            <span class="badge badge-danger">INOPERATIVO</span>
                        @else
                            <span class="badge badge-neutral">{{ $lector }}</span>
                        @endif
                    </td>
                    <td class="lbl-col">VELOCIDAD MEDIDA:</td>
                    <td class="val-col">
                        @if(!empty($contenido['velocidad_descarga']) || !empty($contenido['velocidad_subida']))
                            <span style="color: #1e3a8a; font-weight: 700;">
                                Descarga: {{ $contenido['velocidad_descarga'] ?? '--' }} Mbps &nbsp;|&nbsp; Subida: {{ $contenido['velocidad_subida'] ?? '--' }} Mbps
                            </span>
                        @else
                            <span style="color: #94a3b8;">-- Mbps</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    {{-- 4. OBSERVACIONES Y HALLAZGOS TÉCNICOS --}}
    <div class="card-section">
        <div class="card-header">
            <span class="num-pill">{{ $hasComputoPdf ? 4 : 3 }}</span> OBSERVACIONES Y HALLAZGOS T&Eacute;CNICOS
        </div>
        <div class="obs-box">
            {{ !empty($contenido['observaciones']) ? strtoupper($contenido['observaciones']) : 'SIN OBSERVACIONES O INCIDENCIAS REPORTADAS EN ESTE CONSULTORIO.' }}
        </div>
    </div>

    {{-- 5. REGISTRO FOTOGRÁFICO --}}
    @php
        $evidenciaPath = $detalle->contenido['evidencia_path'] ?? $contenido['evidencia_path'] ?? '';
    @endphp
    <div class="card-section">
        <div class="card-header">
            <span class="num-pill">{{ $hasComputoPdf ? 5 : 4 }}</span> REGISTRO FOTOGR&Aacute;FICO / EVIDENCIA ADJUNTA
        </div>
        <div class="photo-wrapper">
            @if(!empty($evidenciaPath) && file_exists(storage_path('app/public/' . $evidenciaPath)))
                <img src="{{ storage_path('app/public/' . $evidenciaPath) }}" class="photo-img">
                <div class="photo-meta">
                    EVIDENCIA REGISTRADA: {{ strtoupper(basename($evidenciaPath)) }}
                </div>
            @else
                <div class="no-photo-box">
                    No se adjunt&oacute; evidencia fotogr&aacute;fica para este consultorio.
                </div>
            @endif
        </div>
    </div>

    {{-- 6. FIRMAS DE CONFORMIDAD --}}
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
                        <div class="sig-name">
                            @if($acta->user)
                                {{ strtoupper($acta->user->name . ' ' . $acta->user->apellido_paterno . ' ' . $acta->user->apellido_materno) }}
                            @else
                                EQUIPO T&Eacute;CNICO DE MONITOREO TI
                            @endif
                        </div>
                        <div class="sig-role">MONITOR / IMPLEMENTADOR TI</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
