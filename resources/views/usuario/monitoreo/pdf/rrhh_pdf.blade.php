<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Padrón de Recursos Humanos por Servicio - Acta #{{ $acta->numero_acta }}</title>
    <style>
        @page { 
            margin: 1cm 1.2cm 1.2cm 1.2cm; 
        }
        body { 
            font-family: 'Helvetica', Arial, sans-serif; 
            font-size: 8.5px; 
            color: #1e293b; 
            line-height: 1.35; 
            background-color: #ffffff;
        }

        /* HEADER BANNER */
        .banner-container {
            background-color: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 12px;
        }
        .banner-table {
            width: 100%;
            border-collapse: collapse;
        }
        .banner-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .tag-badge {
            background-color: #7c3aed;
            color: #ffffff;
            font-size: 7.5px;
            font-weight: 900;
            padding: 2.5px 7px;
            border-radius: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        .acta-tag {
            color: #94a3b8;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            margin-left: 6px;
        }
        .banner-title {
            font-size: 15px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: -0.3px;
            margin-top: 3px;
            margin-bottom: 2px;
        }
        .banner-sub {
            font-size: 8.5px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
        }

        /* TABLA DE TRABAJADORES */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 8px;
        }
        table.data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 7.5px;
            font-weight: 900;
            text-transform: uppercase;
            padding: 5px 6px;
            text-align: left;
            border: 1px solid #0f172a;
        }
        table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            font-size: 8px;
            vertical-align: middle;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 7px;
            font-weight: 800;
            text-transform: uppercase;
            text-align: center;
        }
        .badge-servicio { background-color: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; }
        .badge-serums { background-color: #d1fae5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-no-serums { background-color: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

        /* OBSERVACIONES BOX */
        .obs-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background-color: #f8fafc;
            padding: 8px 10px;
            margin-top: 8px;
        }
        .obs-title {
            font-size: 7.5px;
            font-weight: 900;
            color: #475569;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .obs-content {
            font-size: 8px;
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
        }

        /* FOOTER */
        .footer-bar {
            position: fixed;
            bottom: -0.6cm;
            left: 0;
            right: 0;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            font-size: 7px;
            color: #94a3b8;
            text-align: center;
            text-transform: uppercase;
            font-weight: 700;
        }
    </style>
</head>
<body>

    {{-- ENCABEZADO SUPERIOR TIPO FORMULARIO --}}
    <div class="banner-container">
        <table class="banner-table">
            <tr>
                <td>
                    <div>
                        <span class="tag-badge">Módulo Fijo</span>
                        <span class="acta-tag">ID Acta: #{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="banner-title">PADRÓN DE RECURSOS HUMANOS POR SERVICIO (RR.HH)</div>
                    <div class="banner-sub">
                        IPRESS: {{ $acta->establecimiento->codigo ?? 'S/C' }} — {{ strtoupper($acta->establecimiento->nombre) }} &bull; RED: {{ strtoupper($acta->establecimiento->red ?? 'GENERAL') }}
                    </div>
                </td>
                <td style="text-align: right; width: 30%;">
                    <div style="font-size: 8px; font-weight: 800; color: #475569; text-transform: uppercase;">FECHA: {{ date('d/m/Y') }}</div>
                    <div style="font-size: 7.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-top: 2px;">
                        TOTAL: {{ count($trabajadores) }} TRABAJADORES
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- TABLA DE PERSONAL REGISTRADO --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20px; text-align: center;">#</th>
                <th style="width: 80px;">Servicio</th>
                <th style="width: 70px;">Doc. Identidad</th>
                <th>Apellidos y Nombres</th>
                <th style="width: 110px;">Profesión</th>
                <th style="width: 85px;">Colegiatura / RNE</th>
                <th style="width: 110px;">Contacto</th>
                <th style="width: 65px; text-align: center;">SERUMS</th>
                <th style="width: 55px; text-align: center;">Periodo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trabajadores as $index => $t)
                <tr>
                    <td style="text-align: center; font-weight: 900; color: #64748b;">{{ $index + 1 }}</td>
                    <td>
                        <span class="badge badge-servicio">{{ $t['servicio'] ?? 'MEDICINA' }}</span>
                    </td>
                    <td>
                        <span style="font-size: 6.5px; color: #94a3b8; display: block;">{{ $t['tipo_doc'] ?? 'DNI' }}</span>
                        <b>{{ $t['doc'] ?? '' }}</b>
                    </td>
                    <td>
                        <b style="color: #0f172a; text-transform: uppercase;">
                            {{ $t['apellido_paterno'] ?? '' }} {{ $t['apellido_materno'] ?? '' }}, {{ $t['nombres'] ?? '' }}
                        </b>
                    </td>
                    <td>
                        <span style="font-size: 7.5px; font-weight: 800; color: #334155; text-transform: uppercase;">
                            {{ $t['profesion'] ?? 'NO ESPECIFICADO' }}
                        </span>
                    </td>
                    <td>
                        @if(!empty($t['colegiatura']))
                            <div style="font-size: 7.5px; font-weight: 800; color: #475569;">{{ !empty($t['colegio_profesional']) ? $t['colegio_profesional'] . ' ' : '' }}{{ $t['colegiatura'] }}</div>
                        @endif
                        @if(!empty($t['rne']))
                            <div style="font-size: 7px; font-weight: 800; color: #7c3aed;">RNE: {{ $t['rne'] }}</div>
                        @endif
                        @if(empty($t['colegiatura']) && empty($t['rne']))
                            <span style="color: #94a3b8; font-size: 7px;">S/N</span>
                        @endif
                    </td>
                    <td>
                        @if(!empty($t['celular']))
                            <div style="font-size: 7.5px; font-weight: 700; color: #334155;">Telf: {{ $t['celular'] }}</div>
                        @endif
                        @if(!empty($t['correo']))
                            <div style="font-size: 6.5px; color: #64748b; text-transform: lowercase;">{{ $t['correo'] }}</div>
                        @endif
                        @if(empty($t['celular']) && empty($t['correo']))
                            <span style="color: #cbd5e1; font-size: 7px;">Sin datos</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if(($t['es_serums'] ?? '') === 'SI')
                            <span class="badge badge-serums">SÍ</span>
                        @else
                            <span class="badge badge-no-serums">NO</span>
                        @endif
                    </td>
                    <td style="text-align: center; font-weight: 800; color: #4338ca;">
                        {{ ($t['es_serums'] ?? '') === 'SI' ? ($t['periodo_serums'] ?? 'S/P') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 16px; color: #94a3b8; font-weight: 800;">
                        NO SE REGISTRARON TRABAJADORES EN EL PADRÓN DE RECURSOS HUMANOS
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- OBSERVACIONES --}}
    @if(!empty($contenido['observaciones']))
        <div class="obs-card">
            <div class="obs-title">Observaciones / Notas de Recursos Humanos:</div>
            <div class="obs-content">{{ $contenido['observaciones'] }}</div>
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
        <div style="margin-top: 10px; page-break-inside: avoid;">
            <div style="font-size: 8px; font-weight: 900; color: #475569; text-transform: uppercase; margin-bottom: 5px;">EVIDENCIA FOTOGRÁFICA:</div>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    @if($foto1Base64)
                        <td style="width: {{ $foto2Base64 ? '50%' : '100%' }}; padding-right: 6px; text-align: center; border: none; vertical-align: top;">
                            <img src="{{ $foto1Base64 }}" style="max-height: 180px; max-width: 100%; border-radius: 6px; border: 1px solid #cbd5e1;">
                            <div style="font-size: 7px; font-weight: 800; color: #64748b; margin-top: 3px; text-transform: uppercase;">Evidencia #1</div>
                        </td>
                    @endif
                    @if($foto2Base64)
                        <td style="width: {{ $foto1Base64 ? '50%' : '100%' }}; padding-left: 6px; text-align: center; border: none; vertical-align: top;">
                            <img src="{{ $foto2Base64 }}" style="max-height: 180px; max-width: 100%; border-radius: 6px; border: 1px solid #cbd5e1;">
                            <div style="font-size: 7px; font-weight: 800; color: #64748b; margin-top: 3px; text-transform: uppercase;">Evidencia #2</div>
                        </td>
                    @endif
                </tr>
            </table>
        </div>
    @endif

    {{-- PIE DE PÁGINA --}}
    <div class="footer-bar">
        SISTEMA DE MONITOREO Y EVALUACIÓN DE ESTABLECIMIENTOS DE SALUD &bull; REPORTE OFICIAL DE RECURSOS HUMANOS &bull; ACTA #{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}
    </div>

</body>
</html>
