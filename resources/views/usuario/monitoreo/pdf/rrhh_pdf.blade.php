<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Padrón de Recursos Humanos por Servicio - Acta #{{ $acta->numero_acta }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap');

        @page {
            margin: 1cm 1.2cm 1.2cm 1.2cm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', 'Segoe UI', 'Century Gothic', 'Calibri', 'Helvetica Neue', 'Arial', sans-serif;
            font-size: 7.5px;
            color: #1e293b;
            line-height: 1.4;
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
            margin-bottom: 10px;
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
            font-size: 13px;
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
            padding: 2px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .eess-info {
            font-size: 7.5px;
            font-weight: 700;
            color: #475569;
            margin-top: 3px;
            text-transform: uppercase;
        }
        .acta-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 5px 12px;
            text-align: center;
        }
        .acta-box-num {
            font-size: 12px;
            font-weight: 800;
            color: #0f2b5c;
            letter-spacing: 0.5px;
        }
        .acta-box-lbl {
            font-size: 6.5px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .total-badge {
            display: inline-block;
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
            padding: 1.5px 6px;
            border-radius: 3px;
            font-size: 6.5px;
            font-weight: 800;
            margin-top: 2px;
            text-transform: uppercase;
        }

        /* SECCIONES Y TABLAS */
        .card-section {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            margin-bottom: 10px;
            background-color: #ffffff;
            page-break-inside: avoid;
        }
        .card-header {
            background-color: #0f2b5c;
            color: #ffffff;
            padding: 4px 8px;
            font-size: 7.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }

        /* TABLA PRINCIPAL DE TRABAJADORES */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.data-table th {
            background-color: #0f2b5c;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 7px;
            letter-spacing: 0.3px;
            padding: 5px 6px;
            border: 1px solid #0f2b5c;
            text-align: left;
        }
        table.data-table td {
            border: 1px solid #e2e8f0;
            padding: 4.5px 6px;
            font-size: 7px;
            vertical-align: middle;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 1.5px 5px;
            border-radius: 3px;
            font-size: 6.5px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
        }
        .badge-servicio { background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-serums { background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-no-serums { background-color: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
        .badge-rne { background-color: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }

        /* OBSERVACIONES */
        .obs-card {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background-color: #ffffff;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .obs-title {
            background-color: #f1f5f9;
            color: #334155;
            padding: 4px 8px;
            font-size: 7px;
            font-weight: 800;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }
        .obs-content {
            padding: 6px 8px;
            font-size: 7.5px;
            color: #0f172a;
            font-weight: 600;
            line-height: 1.4;
        }

        /* FOTOS */
        .photo-card {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background-color: #f8fafc;
            padding: 6px;
            text-align: center;
        }
        .photo-img {
            max-height: 150px;
            max-width: 100%;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            padding: 2px;
        }
        .photo-label {
            font-size: 6.5px;
            font-weight: 700;
            color: #0f2b5c;
            margin-top: 3px;
            text-transform: uppercase;
        }

        /* FOOTER */
        #footer {
            position: fixed;
            bottom: -0.8cm;
            left: 0;
            right: 0;
            height: 18px;
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
        Sistema de Monitoreo y Evaluación de Establecimientos de Salud &bull; Reporte Oficial de Recursos Humanos &bull; Acta #{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }} &bull; EESS: {{ strtoupper($acta->establecimiento->nombre ?? '') }}
    </div>

    {{-- ENCABEZADO INSTITUCIONAL --}}
    <table class="header-table">
        <tr>
            <td style="width: 75%;">
                <div class="inst-title">MINISTERIO DE SALUD &bull; DIRECCI&Oacute;N DE REDES INTEGRADAS DE SALUD</div>
                <div class="main-title">PADR&Oacute;N DE RECURSOS HUMANOS POR SERVICIO (RR.HH)</div>
                <div>
                    <span class="submodule-badge">M&Oacute;DULO FIJO &bull; RECURSOS HUMANOS</span>
                </div>
                <div class="eess-info">
                    <strong>IPRESS:</strong> {{ $acta->establecimiento->codigo ?? 'S/C' }} - {{ strtoupper($acta->establecimiento->nombre) }} &nbsp;|&nbsp; 
                    <strong>RED:</strong> {{ strtoupper($acta->establecimiento->red ?? 'GENERAL') }} &nbsp;|&nbsp;
                    <strong>PROVINCIA:</strong> {{ strtoupper($acta->establecimiento->provincia ?? 'GENERAL') }}
                </div>
            </td>
            <td style="width: 25%;" class="text-right">
                <div class="acta-box">
                    <div class="acta-box-lbl">PADRÓN RRHH</div>
                    <div class="acta-box-num">N&deg; {{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}</div>
                    <div style="font-size: 6.5px; font-weight: 700; color: #475569; margin-top: 1px;">
                        FECHA: {{ date('d/m/Y') }}
                    </div>
                    <div class="total-badge">
                        TOTAL: {{ count($trabajadores) }} TRABAJADORES
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- TABLA DE PERSONAL REGISTRADO --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20px; text-align: center;">#</th>
                <th style="width: 90px;">Servicio</th>
                <th style="width: 80px;">Doc. Identidad</th>
                <th>Apellidos y Nombres</th>
                <th style="width: 120px;">Profesi&oacute;n / Cargo</th>
                <th style="width: 95px;">Colegiatura / RNE</th>
                <th style="width: 120px;">Contacto</th>
                <th style="width: 55px; text-align: center;">SERUMS</th>
                <th style="width: 55px; text-align: center;">Periodo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trabajadores as $index => $t)
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #64748b;">{{ $index + 1 }}</td>
                    <td>
                        <span class="badge badge-servicio">{{ strtoupper($t['servicio'] ?? 'MEDICINA') }}</span>
                    </td>
                    <td>
                        <span style="font-size: 6px; color: #64748b; font-weight: 700; display: block;">{{ $t['tipo_doc'] ?? 'DNI' }}</span>
                        <strong style="color: #0f2b5c;">{{ $t['doc'] ?? '' }}</strong>
                    </td>
                    <td>
                        <strong style="color: #0f172a; text-transform: uppercase;">
                            {{ $t['apellido_paterno'] ?? '' }} {{ $t['apellido_materno'] ?? '' }}, {{ $t['nombres'] ?? '' }}
                        </strong>
                    </td>
                    <td>
                        <span style="font-size: 7px; font-weight: 700; color: #334155; text-transform: uppercase;">
                            {{ $t['profesion'] ?? 'NO ESPECIFICADO' }}
                        </span>
                    </td>
                    <td>
                        @if(!empty($t['colegiatura']))
                            <div style="font-size: 7px; font-weight: 700; color: #475569;">Col: {{ $t['colegiatura'] }}</div>
                        @endif
                        @if(!empty($t['rne']))
                            <div class="badge badge-rne" style="margin-top: 1px;">RNE: {{ $t['rne'] }}</div>
                        @endif
                        @if(empty($t['colegiatura']) && empty($t['rne']))
                            <span style="color: #94a3b8; font-size: 6.5px;">S/N</span>
                        @endif
                    </td>
                    <td>
                        @if(!empty($t['celular']))
                            <div style="font-size: 7px; font-weight: 700; color: #334155;">Telf: {{ $t['celular'] }}</div>
                        @endif
                        @if(!empty($t['correo']))
                            <div style="font-size: 6.5px; color: #64748b; text-transform: lowercase;">{{ $t['correo'] }}</div>
                        @endif
                        @if(empty($t['celular']) && empty($t['correo']))
                            <span style="color: #cbd5e1; font-size: 6.5px;">Sin datos</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if(($t['es_serums'] ?? '') === 'SI')
                            <span class="badge badge-serums">S&Iacute;</span>
                        @else
                            <span class="badge badge-no-serums">NO</span>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: 700; color: #0f2b5c;">
                        {{ ($t['es_serums'] ?? '') === 'SI' ? ($t['periodo_serums'] ?? 'S/P') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 14px; color: #94a3b8; font-weight: 700;">
                        NO SE REGISTRARON TRABAJADORES EN EL PADR&Oacute;N DE RECURSOS HUMANOS
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- OBSERVACIONES --}}
    @if(!empty($contenido['observaciones']))
        <div class="obs-card">
            <div class="obs-title">Observaciones / Notas de Recursos Humanos</div>
            <div class="obs-content">{{ strtoupper($contenido['observaciones']) }}</div>
        </div>
    @endif

    {{-- EVIDENCIA FOTOGRÁFICA --}}
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
        <div class="no-break" style="margin-top: 8px;">
            <div style="font-size: 7px; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 4px;">EVIDENCIA FOTOGR&Aacute;FICA:</div>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    @if($foto1Base64)
                        <td style="width: {{ $foto2Base64 ? '50%' : '100%' }}; padding-right: 5px; border: none; vertical-align: top;">
                            <div class="photo-card">
                                <img src="{{ $foto1Base64 }}" class="photo-img">
                                <div class="photo-label">Evidencia #1 - Recursos Humanos</div>
                            </div>
                        </td>
                    @endif
                    @if($foto2Base64)
                        <td style="width: {{ $foto1Base64 ? '50%' : '100%' }}; padding-left: 5px; border: none; vertical-align: top;">
                            <div class="photo-card">
                                <img src="{{ $foto2Base64 }}" class="photo-img">
                                <div class="photo-label">Evidencia #2 - Recursos Humanos</div>
                            </div>
                        </td>
                    @endif
                </tr>
            </table>
        </div>
    @endif

</body>
</html>
