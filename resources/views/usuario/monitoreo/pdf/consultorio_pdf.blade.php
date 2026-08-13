<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Evaluación de Consultorio: {{ $contenido['titulo_consultorio'] ?? 'Consultorio' }} - Acta {{ $acta->numero_acta }}</title>
    <style>
        @page { 
            margin: 1.2cm 1.5cm 1.5cm 1.5cm; 
        }
        body { 
            font-family: 'Helvetica', Arial, sans-serif; 
            font-size: 9px; 
            color: #1e293b; 
            line-height: 1.4; 
            background-color: #ffffff;
        }

        /* HEADER BANNER ESTILO FORMULARIO */
        .banner-container {
            background-color: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 12px 16px;
            margin-bottom: 14px;
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
            background-color: #4f46e5;
            color: #ffffff;
            font-size: 7.5px;
            font-weight: 900;
            padding: 3px 8px;
            border-radius: 6px;
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
            font-size: 16px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: -0.3px;
            margin-top: 4px;
            margin-bottom: 2px;
        }
        .banner-sub {
            font-size: 8.5px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
        }

        /* SECCIONES ESTILO FORMULARIO */
        .section-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 14px;
            margin-bottom: 12px;
        }
        .section-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 6px;
        }
        .section-header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .section-number {
            background-color: #4f46e5;
            color: #ffffff;
            width: 22px;
            height: 22px;
            line-height: 22px;
            text-align: center;
            border-radius: 50%;
            font-weight: 900;
            font-size: 10px;
            display: inline-block;
        }
        .section-title-text {
            font-size: 11px;
            font-weight: 900;
            color: #1e1b4b;
            text-transform: uppercase;
            letter-spacing: -0.2px;
            margin-left: 8px;
        }

        /* GRID SIMULADO EN TABLAS DE FORMULARIO */
        .form-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-left: -8px;
            margin-right: -8px;
        }
        .form-grid td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .field-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 10px;
        }
        .field-label {
            font-size: 7px;
            font-weight: 900;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
            display: block;
        }
        .field-value {
            font-size: 9px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }

        /* TARJETAS SELECCIONABLES (TIPO CONECTIVIDAD / TIPO CONSULTORIO) */
        .card-option {
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 10px;
            background-color: #ffffff;
            margin-bottom: 6px;
        }
        .card-option-active {
            border: 1.5px solid #4f46e5;
            background-color: #eeef4ff2; /* soft indigo */
            background: #e0e7ff;
        }
        .card-option-active-green {
            border: 1.5px solid #059669;
            background-color: #d1fae5;
        }

        /* TABLA DE EQUIPOS */
        table.equipos-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 4px;
        }
        table.equipos-table th {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            color: #475569;
            font-size: 7.5px;
            font-weight: 900;
            text-transform: uppercase;
            padding: 5px 6px;
            text-align: left;
        }
        table.equipos-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            font-size: 8.5px;
            vertical-align: middle;
        }

        /* BADGES */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5px;
            font-weight: 800;
            text-transform: uppercase;
            text-align: center;
        }
        .badge-operativo { background-color: #d1fae5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-regular { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-inoperativo { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        .badge-propio { background-color: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }

        /* PREVIEW FOTO EVIDENCIA */
        .evidence-card {
            border: 1.5px solid #c7d2fe;
            border-radius: 10px;
            background-color: #f8fafc;
            padding: 8px;
            text-align: center;
            margin-top: 8px;
        }
        .evidence-img {
            max-width: 100%;
            max-height: 250px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
        }

        /* FOOTER */
        .footer-bar {
            position: fixed;
            bottom: -0.7cm;
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

    @php 
        $n = 1; 
        $contenido = $detalle->contenido ?? [];
    @endphp

    {{-- ENCABEZADO SUPERIOR TIPO FORMULARIO --}}
    <div class="banner-container">
        <table class="banner-table">
            <tr>
                <td>
                    <div>
                        <span class="tag-badge">Módulo de Evaluación</span>
                        <span class="acta-tag">ID Acta: #{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="banner-title">{{ $contenido['titulo_consultorio'] ?? 'CONSULTORIO / MÓDULO' }}</div>
                    <div class="banner-sub">
                        EESS: {{ $acta->establecimiento->codigo ?? 'S/C' }} — {{ strtoupper($acta->establecimiento->nombre) }}
                    </div>
                </td>
                <td style="text-align: right; width: 35%;">
                    <div style="font-size: 8px; font-weight: 800; color: #475569; text-transform: uppercase;">MINISTERIO DE SALUD</div>
                    <div style="font-size: 7.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-top: 2px;">
                        PROVINCIA: {{ strtoupper($acta->establecimiento->provincia ?? 'GENERAL') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- 1.- DATOS GENERALES --}}
    <div class="section-card">
        <table class="section-header-table">
            <tr>
                <td style="width: 26px;"><span class="section-number">1</span></td>
                <td><span class="section-title-text">DATOS GENERALES</span></td>
            </tr>
        </table>

        <table class="form-grid">
            <tr>
                <td style="width: 33%;">
                    <div class="field-box">
                        <span class="field-label">Fecha de Monitoreo</span>
                        <span class="field-value">{{ isset($contenido['fecha']) ? date('d/m/Y', strtotime($contenido['fecha'])) : date('d/m/Y') }}</span>
                    </div>
                </td>
                <td style="width: 33%;">
                    <div class="field-box">
                        <span class="field-label">Turno Evaluado</span>
                        <span class="field-value">{{ $contenido['turno'] ?? 'MAÑANA' }}</span>
                    </div>
                </td>
                <td style="width: 34%;">
                    <div class="field-box" style="border-color: #6366f1; background-color: #eeef4ff2; background: #f5f3ff;">
                        <span class="field-label" style="color: #4f46e5;">Tipo de Consultorio</span>
                        <span class="field-value" style="color: #4338ca;">{{ $contenido['tipo_consultorio'] ?? 'FISICO' }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- 2.- EQUIPOS DE CÓMPUTO E IMPRESORA --}}
    <div class="section-card">
        <table class="section-header-table">
            <tr>
                <td style="width: 26px;"><span class="section-number">2</span></td>
                <td><span class="section-title-text">EQUIPOS DE CÓMPUTO E IMPRESORA</span></td>
            </tr>
        </table>

        @php
            $equipos = \App\Models\EquipoComputo::where('cabecera_monitoreo_id', $acta->id)
                ->where('modulo', $detalle->modulo_nombre ?? '')
                ->get();
        @endphp

        @if(count($equipos) > 0)
            <table class="equipos-table">
                <thead>
                    <tr>
                        <th style="width: 4%; text-align: center;">N°</th>
                        <th style="width: 32%;">DESCRIPCIÓN</th>
                        <th style="width: 7%; text-align: center;">CANT.</th>
                        <th style="width: 14%; text-align: center;">ESTADO</th>
                        <th style="width: 14%;">PROPIEDAD</th>
                        <th style="width: 17%;">N.SERIE / C.PAT</th>
                        <th style="width: 12%;">OBSERVACIÓN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($equipos as $idx => $eq)
                        @php
                            $estClass = 'badge-operativo';
                            if ($eq->estado == 'REGULAR') $estClass = 'badge-regular';
                            if ($eq->estado == 'INOPERATIVO') $estClass = 'badge-inoperativo';
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: 800;">{{ $idx + 1 }}</td>
                            <td style="font-weight: 800; text-transform: uppercase;">{{ $eq->descripcion }}</td>
                            <td style="text-align: center; font-weight: 800;">{{ $eq->cantidad ?? 1 }}</td>
                            <td style="text-align: center;">
                                <span class="badge {{ $estClass }}">{{ $eq->estado }}</span>
                            </td>
                            <td style="text-transform: uppercase;">
                                <span class="badge badge-propio">{{ $eq->propio ?? 'EXCLUSIVO' }}</span>
                            </td>
                            <td style="font-weight: 800; color: #4338ca; text-transform: uppercase;">{{ $eq->nro_serie ?: 'S/N' }}</td>
                            <td style="text-transform: uppercase; color: #64748b; font-size: 7.5px;">{{ $eq->observacion ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="background-color: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px; padding: 10px; text-align: center; color: #94a3b8; font-size: 8px; font-weight: 800; text-transform: uppercase;">
                No se registraron equipos en este consultorio.
            </div>
        @endif
    </div>

    {{-- 3.- TIPO DE CONECTIVIDAD --}}
    @php
        $tipoConn = $contenido['tipo_conectividad'] ?? 'CABLEADO';
    @endphp
    <div class="section-card">
        <table class="section-header-table">
            <tr>
                <td style="width: 26px;"><span class="section-number">3</span></td>
                <td><span class="section-title-text">TIPO DE CONECTIVIDAD</span></td>
            </tr>
        </table>

        {{-- TARJETAS DE SELECCIÓN DE RED --}}
        <table class="form-grid" style="margin-bottom: 8px;">
            <tr>
                <td style="width: 33%;">
                    <div class="card-option {{ $tipoConn == 'WIFI' ? 'card-option-active' : '' }}">
                        <div style="font-size: 9px; font-weight: 900; color: #0f172a; text-transform: uppercase;">WIFI</div>
                        <div style="font-size: 7.5px; font-weight: 800; color: #4f46e5; text-transform: uppercase; margin-top: 1px;">Inalámbrico</div>
                    </div>
                </td>
                <td style="width: 33%;">
                    <div class="card-option {{ $tipoConn == 'CABLEADO' ? 'card-option-active' : '' }}">
                        <div style="font-size: 9px; font-weight: 900; color: #0f172a; text-transform: uppercase;">CABLEADO</div>
                        <div style="font-size: 7.5px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-top: 1px;">Ethernet</div>
                    </div>
                </td>
                <td style="width: 34%;">
                    <div class="card-option {{ $tipoConn == 'SIN CONECTIVIDAD' ? 'card-option-active' : '' }}">
                        <div style="font-size: 9px; font-weight: 900; color: #0f172a; text-transform: uppercase;">SIN CONECTIVIDAD</div>
                        <div style="font-size: 7.5px; font-weight: 800; color: #dc2626; text-transform: uppercase; margin-top: 1px;">No cuenta con internet</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- SUB CAMPOS CONECTIVIDAD --}}
        <table class="form-grid">
            <tr>
                <td style="width: 33%;">
                    <div class="field-box">
                        <span class="field-label">Operador de Servicio</span>
                        <span class="field-value">{{ $contenido['operador_servicio'] ?? 'NO REGISTRADO' }}</span>
                    </div>
                </td>
                <td style="width: 33%;">
                    <div class="field-box">
                        <span class="field-label">Lector de DNIe</span>
                        <span class="field-value">{{ $contenido['lector_dnie'] ?? 'OPERATIVO' }}</span>
                    </div>
                </td>
                <td style="width: 34%;">
                    <div class="field-box">
                        <span class="field-label">Velocidad de Internet</span>
                        <span class="field-value">
                            @if(!empty($contenido['velocidad_descarga']) || !empty($contenido['velocidad_subida']))
                                {{ $contenido['velocidad_descarga'] ?? '--' }} Mbps (Descarga) / {{ $contenido['velocidad_subida'] ?? '--' }} Mbps (Subida)
                            @else
                                -- Mbps
                            @endif
                        </span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- 4.- OBSERVACIONES Y EVIDENCIAS --}}
    <div class="section-card">
        <table class="section-header-table">
            <tr>
                <td style="width: 26px;"><span class="section-number">4</span></td>
                <td><span class="section-title-text">OBSERVACIONES Y EVIDENCIAS</span></td>
            </tr>
        </table>

        <div style="margin-bottom: 8px;">
            <span class="field-label" style="margin-bottom: 4px;">Observaciones Generales</span>
            <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 10px; font-size: 8.5px; font-weight: 700; color: #1e293b; text-transform: uppercase; min-height: 28px;">
                {{ $contenido['observaciones'] ?? 'SIN OBSERVACIONES O INCIDENCIAS REGISTRADAS EN ESTE CONSULTORIO' }}
            </div>
        </div>

        @php
            $evidenciaPath = $detalle->contenido['evidencia_path'] ?? $contenido['evidencia_path'] ?? '';
        @endphp

        <div style="margin-top: 8px;">
            <span class="field-label" style="margin-bottom: 4px;">Fotografía / Evidencia Adjunta</span>
            @if(!empty($evidenciaPath) && file_exists(storage_path('app/public/' . $evidenciaPath)))
                <div class="evidence-card">
                    <img src="{{ storage_path('app/public/' . $evidenciaPath) }}" class="evidence-img">
                    <div style="font-size: 7.5px; font-weight: 900; color: #4338ca; text-transform: uppercase; margin-top: 6px;">
                        EVIDENCIA FOTOGRÁFICA REGISTRADA: {{ strtoupper(basename($evidenciaPath)) }}
                    </div>
                </div>
            @else
                <div style="background-color: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px; padding: 10px; text-align: center; color: #94a3b8; font-size: 8px; font-weight: 800; text-transform: uppercase;">
                    Sin evidencia fotográfica adjunta para este consultorio.
                </div>
            @endif
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer-bar">
        Sistema de Evaluación & Monitoreo TI — EESS: {{ $acta->establecimiento->nombre ?? '' }} — Reporte Generado el {{ date('d/m/Y H:i:s') }}
    </div>

</body>
</html>
