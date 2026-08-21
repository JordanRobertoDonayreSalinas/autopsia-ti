<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta Consolidada de Diagnóstico Situacional N° {{ ltrim($acta->numero_acta, '0') }}</title>
    <style>
        /* ═══════════════════════════════════════════════════════════════
           REPORTE CONSOLIDADO DE DIAGNÓSTICO SITUACIONAL IPRESS
           Diseño Ejecutivo, Resumido e Institucional
           Tipografía: DejaVu Sans (Nativa DomPDF TrueType)
           ═══════════════════════════════════════════════════════════════ */
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
        .badge-si { background-color: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .badge-no { background-color: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

        /* ── TABLAS EJECUTIVAS ── */
        table.summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
            margin-bottom: 3px;
        }
        table.summary-table th {
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
        table.summary-table td {
            border: 1px solid #e2e8f0;
            padding: 3.5px 5px;
            font-size: 7.5px;
            vertical-align: middle;
            color: #334155;
        }
        table.summary-table tr:nth-child(even) {
            background-color: #fafbff;
        }

        /* ── TARJETAS KPI DE CONECTIVIDAD ── */
        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 5px 6px;
            text-align: center;
        }
        .kpi-card-active {
            background-color: #eef2ff;
            border-color: #c7d2fe;
        }
        .kpi-title {
            font-size: 6px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .kpi-val {
            font-size: 9px;
            font-weight: bold;
            color: #4f46e5;
            margin-top: 1px;
            text-transform: uppercase;
        }

        /* ── PANEL FOTOGRÁFICO ── */
        .photo-card {
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            background-color: #fafbff;
            padding: 4px;
            text-align: center;
        }
        .photo-img {
            width: 100%;
            max-height: 110px;
            object-fit: cover;
            border-radius: 3px;
            border: 1px solid #cbd5e1;
        }
        .photo-caption {
            font-size: 6.5px;
            font-weight: bold;
            color: #4f46e5;
            text-transform: uppercase;
            margin-top: 2px;
            letter-spacing: 0.2px;
        }

        /* ── FIRMAS DE CONFORMIDAD ── */
        .firmas-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 6px;
            margin-top: 4px;
            page-break-inside: avoid;
        }
        .firmas-table td {
            border: none;
            padding: 0;
            vertical-align: top;
            width: 50%;
        }
        .firma-box {
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            background-color: #ffffff;
            padding: 6px 10px 8px 10px;
            text-align: center;
        }
        .firma-space {
            height: 90px;
        }
        .firma-line {
            border-top: 1px solid #94a3b8;
            margin: 0 auto 5px auto;
            width: 90%;
        }
        .firma-name {
            font-size: 7.5px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            display: block;
        }
        .firma-role {
            font-size: 6.5px;
            color: #64748b;
            text-transform: uppercase;
            display: block;
            margin-top: 1px;
        }

        /* ── FOOTER FIJO INSTITUCIONAL ── */
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

    {{-- BARRA SUPERIOR DECORATIVA --}}
    <div class="top-accent"></div>

    @php
        $secNum = 1;
        $creador = $acta->user;
        $monitorNombre = $monitor['nombre'] ?? ($creador ? mb_strtoupper("{$creador->apellido_paterno} {$creador->apellido_materno} {$creador->name}", 'UTF-8') : 'USUARIO NO IDENTIFICADO');
        $jefeNombre = $jefe['nombre'] ?? mb_strtoupper($acta->responsable ?? 'NO REGISTRADO', 'UTF-8');

        // Normalizar colección de módulos
        $modulosCol = collect($modulos ?? []);

        // Mapa slug => contenido de cada módulo, usado para resolver lo que un
        // consultorio FUNCIONAL vinculado a un físico hereda de él (ver
        // MonitoreoModuloGenericController::resolverVinculacion, mismo criterio
        // replicado aquí porque este PDF consolidado no pasa por ese controlador).
        $contenidoPorSlug = [];
        foreach ($modulosCol as $m) {
            $cRaw = $m->contenido;
            $cDecoded = is_array($cRaw) ? $cRaw : (is_string($cRaw) ? json_decode($cRaw, true) : []);
            $contenidoPorSlug[strtolower($m->modulo_nombre ?? '')] = is_array($cDecoded) ? $cDecoded : [];
        }
        $camposInfraHeredables = [
            'cuenta_electricidad',
            'tiene_toma_estabilizada', 'toma_estabilizada_internas', 'toma_estabilizada_externas',
            'tiene_toma_comercial', 'toma_comercial_internas', 'toma_comercial_externas',
            'cuenta_punto_red', 'cantidad_puntos_red',
            'tipo_conectividad', 'operador_servicio', 'operador',
            'velocidad_descarga', 'velocidad_descarga_unidad', 'velocidad_subida', 'velocidad_subida_unidad',
        ];

        // Separar consultorios clínicos/dinámicos del módulo de RRHH y Croquis
        $consultoriosLista = [];
        $rrhhModulo = null;
        $croquisModulo = null;
        $fotosConsolidadas = [];

        foreach ($modulosCol as $modItem) {
            $slug = strtolower($modItem->modulo_nombre ?? '');
            if ($slug === 'config_modulos') continue;

            $rawCont = $modItem->contenido;
            $cont = is_array($rawCont) ? $rawCont : (is_string($rawCont) ? json_decode($rawCont, true) : []);
            if (!is_array($cont)) $cont = [];

            // Si es FUNCIONAL vinculado a un físico, heredar su infraestructura
            // (electricidad/tomas/punto de red/conectividad) y, si además marcó
            // que comparte equipo, resolver de qué slug leer los equipos.
            $slugEquiposEfectivo = $slug;
            if (strtoupper($cont['tipo_consultorio'] ?? '') === 'FUNCIONAL' && !empty($cont['consultorio_vinculado'])) {
                $slugVinculado = strtolower(trim($cont['consultorio_vinculado']));
                // No encadenar: el vinculado debe ser FISICO (o legado sin tipo), nunca otro FUNCIONAL.
                if (isset($contenidoPorSlug[$slugVinculado]) && strtoupper($contenidoPorSlug[$slugVinculado]['tipo_consultorio'] ?? '') !== 'FUNCIONAL') {
                    $contVinculado = $contenidoPorSlug[$slugVinculado];
                    foreach ($camposInfraHeredables as $campo) {
                        $cont[$campo] = $contVinculado[$campo] ?? null;
                    }
                    if (strtoupper($cont['comparte_equipo_con_fisico'] ?? 'NO') === 'SI') {
                        $slugEquiposEfectivo = $slugVinculado;
                    }
                }
            }

            if ($slug === 'rrhh') {
                $rrhhModulo = [
                    'item' => $modItem,
                    'contenido' => $cont
                ];
                if (!empty($cont['foto_1'])) {
                    $p = storage_path('app/public/' . $cont['foto_1']);
                    if (file_exists($p)) {
                        $fotosConsolidadas[] = [
                            'titulo' => 'RR.HH. — FOTO 1',
                            'src'    => 'data:image/jpeg;base64,' . base64_encode(file_get_contents($p))
                        ];
                    }
                }
                if (!empty($cont['foto_2'])) {
                    $p = storage_path('app/public/' . $cont['foto_2']);
                    if (file_exists($p)) {
                        $fotosConsolidadas[] = [
                            'titulo' => 'RR.HH. — FOTO 2',
                            'src'    => 'data:image/jpeg;base64,' . base64_encode(file_get_contents($p))
                        ];
                    }
                }
            } elseif ($slug === 'infraestructura_2d') {
                $croquisModulo = [
                    'item' => $modItem,
                    'contenido' => $cont
                ];
            } else {
                // Consultorio o módulo asistencial
                $tituloCons = !empty($cont['titulo_consultorio']) 
                    ? strtoupper($cont['titulo_consultorio']) 
                    : strtoupper(str_replace(['_', '-'], ' ', preg_replace('/_\d+$/', '', $slug)));
                
                $consultoriosLista[] = [
                    'slug'      => $slug,
                    'slug_equipos' => $slugEquiposEfectivo,
                    'titulo'    => $tituloCons,
                    'servicio'  => strtoupper($cont['servicio_asociado'] ?? 'GENERAL'),
                    'departamento' => strtoupper($cont['departamento_asociado'] ?? ''),
                    'tipo'      => strtoupper($cont['tipo_consultorio'] ?? 'FÍSICO'),
                    'piso'      => is_numeric($cont['piso'] ?? '') ? ('PISO ' . $cont['piso']) : strtoupper($cont['piso'] ?? 'PISO 1'),
                    'electricidad' => strtoupper($cont['cuenta_electricidad'] ?? 'SI'),
                    'punto_red' => strtoupper($cont['cuenta_punto_red'] ?? 'SI'),
                    'cant_puntos' => $cont['cantidad_puntos_red'] ?? null,
                    'conectividad' => strtoupper($cont['tipo_conectividad'] ?? 'SIN CONEXIÓN'),
                    'isp'       => strtoupper($cont['operador_servicio'] ?? ($cont['operador'] ?? 'N/A')),
                    'descarga'  => !empty($cont['velocidad_descarga']) ? ($cont['velocidad_descarga'] . ' ' . ($cont['velocidad_descarga_unidad'] ?? 'Mbps')) : '',
                    'subida'    => !empty($cont['velocidad_subida']) ? ($cont['velocidad_subida'] . ' ' . ($cont['velocidad_subida_unidad'] ?? 'Mbps')) : '',
                    'observaciones' => $cont['observaciones'] ?? '',
                    'evidencia_path'=> ($cont['evidencias'][0]['path'] ?? null) ?? ($cont['evidencia_path_1'] ?? ($cont['evidencia_path'] ?? ($cont['foto_evidencia'] ?? '')))
                ];

                // Extraer foto de evidencia del consultorio (primera foto de la lista;
                // el consolidado resume con una sola imagen por consultorio, ver
                // consultorio_pdf.blade.php para las hasta 10 fotos completas con
                // su descripcion)
                $evPath = ($cont['evidencias'][0]['path'] ?? null) ?? ($cont['evidencia_path_1'] ?? ($cont['evidencia_path'] ?? ($cont['foto_evidencia'] ?? '')));
                if (!empty($evPath)) {
                    $p = storage_path('app/public/' . $evPath);
                    if (file_exists($p)) {
                        $fotosConsolidadas[] = [
                            'titulo' => 'EVIDENCIA: ' . $tituloCons,
                            'src'    => 'data:image/jpeg;base64,' . base64_encode(file_get_contents($p))
                        ];
                    }
                }
            }
        }

        // Extraer fotos de cabecera si existen
        if (!empty($acta->foto1)) {
            $p = storage_path('app/public/' . $acta->foto1);
            if (file_exists($p)) {
                $fotosConsolidadas[] = [
                    'titulo' => 'ESTABLECIMIENTO — FOTO 1',
                    'src'    => 'data:image/jpeg;base64,' . base64_encode(file_get_contents($p))
                ];
            }
        }
        if (!empty($acta->foto2)) {
            $p = storage_path('app/public/' . $acta->foto2);
            if (file_exists($p)) {
                $fotosConsolidadas[] = [
                    'titulo' => 'ESTABLECIMIENTO — FOTO 2',
                    'src'    => 'data:image/jpeg;base64,' . base64_encode(file_get_contents($p))
                ];
            }
        }

        // Equipos de cómputo
        $equiposCol = collect($equipos ?? []);
        $totalEquipos = $equiposCol->count();
        $totalOperativos = $equiposCol->filter(fn($e) => strtoupper(trim($e->estado ?? '')) === 'OPERATIVO')->count();
        $totalRegulares  = $equiposCol->filter(fn($e) => strtoupper(trim($e->estado ?? '')) === 'REGULAR')->count();
        $totalInoperativos = $equiposCol->filter(fn($e) => strtoupper(trim($e->estado ?? '')) === 'INOPERATIVO')->count();

        // Análisis de Conectividad si no viene provisto
        if (empty($analisisConectividad) || empty($analisisConectividad['max_descarga'])) {
            $descList = [];
            $subList = [];
            $opList = [];
            $tipoList = [];
            foreach ($consultoriosLista as $cItem) {
                if (!empty($cItem['descarga'])) {
                    $val = floatval($cItem['descarga']);
                    if ($val > 0) $descList[] = $val;
                }
                if (!empty($cItem['subida'])) {
                    $val = floatval($cItem['subida']);
                    if ($val > 0) $subList[] = $val;
                }
                if (!empty($cItem['isp']) && $cItem['isp'] !== 'N/A' && $cItem['isp'] !== 'OTROS') {
                    $opList[] = $cItem['isp'];
                }
                if (!empty($cItem['conectividad']) && $cItem['conectividad'] !== 'SIN CONEXIÓN') {
                    $tipoList[] = $cItem['conectividad'];
                }
            }
            $analisisConectividad = [
                'max_descarga' => count($descList) > 0 ? max($descList) : 0,
                'max_subida'   => count($subList) > 0 ? max($subList) : 0,
                'avg_descarga' => count($descList) > 0 ? round(array_sum($descList) / count($descList), 2) : 0,
                'avg_subida'   => count($subList) > 0 ? round(array_sum($subList) / count($subList), 2) : 0,
                'mod_operador' => count($opList) > 0 ? array_keys(array_count_values($opList), max(array_count_values($opList)))[0] : 'NO ESPECIFICADO',
                'mod_tipo'     => count($tipoList) > 0 ? array_keys(array_count_values($tipoList), max(array_count_values($tipoList)))[0] : 'SIN CONEXIÓN',
            ];
        }

        // Trabajadores de RRHH
        $trabajadores = $rrhhModulo['contenido']['trabajadores'] ?? [];
        $totalPersonal = count($trabajadores);
    @endphp

    {{-- ═══ ENCABEZADO PRINCIPAL ═══ --}}
    <div class="header-block">
        <table class="header-grid">
            <tr>
                {{-- COLUMNA IZQUIERDA: IDENTIFICACIÓN INSTITUCIONAL --}}
                <td style="width: 65%;">
                    <div>
                        <span class="tag-pill">Diagnóstico Situacional IPRESS</span>
                        <span class="tag-acta">Acta N° <strong>#{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}</strong></span>
                    </div>
                    <div class="header-title">REPORTE CONSOLIDADO</div>
                    <div class="header-subtitle">
                        <strong>Establecimiento: </strong>{{ $acta->establecimiento->codigo ?? 'S/C' }} - {{ strtoupper($acta->establecimiento->nombre ?? 'ESTABLECIMIENTO NO REGISTRADO') }}
                        @if(!empty($acta->establecimiento->categoria))
                            &nbsp;({{ strtoupper($acta->establecimiento->categoria) }})
                        @endif
                        <br>
                        <strong>Red:</strong> {{ strtoupper($acta->establecimiento->red ?? 'General') }}
                        @if(!empty($acta->establecimiento->microred))
                            &nbsp;&bull;&nbsp; <strong>Microred:</strong> {{ strtoupper($acta->establecimiento->microred) }}
                        @endif
                        @if(!empty($acta->establecimiento->provincia))
                            &nbsp;&bull;&nbsp; <strong>Provincia:</strong> {{ strtoupper($acta->establecimiento->provincia) }}
                        @endif
                    </div>
                </td>

                {{-- COLUMNA DERECHA: TARJETAS KPI DE CABECERA --}}
                <td style="width: 35%;">
                    <table class="summary-cards">
                        <tr>
                            <td style="width: 33.33%;">
                                <div class="stat-card stat-card-accent">
                                    <span class="stat-value">{{ date('d/m/Y', strtotime($acta->fecha ?? now())) }}</span>
                                    <span class="stat-label">Fecha</span>
                                </div>
                            </td>
                            <td style="width: 33.33%;">
                                <div class="stat-card">
                                    <span class="stat-value">{{ count($consultoriosLista) }}</span>
                                    <span class="stat-label">Consultorios</span>
                                </div>
                            </td>
                            <td style="width: 33.34%;">
                                <div class="stat-card">
                                    <span class="stat-value" style="color: #059669;">{{ $totalEquipos }}</span>
                                    <span class="stat-label">Equipos</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <hr class="header-divider">

    {{-- ═══ 1. INFORMACIÓN DE CONTROL Y CONDICIONES DEL ESTABLECIMIENTO ═══ --}}
    <div class="section-card">
        <table class="section-header-table">
            <tr>
                <td style="width: 20px;"><span class="section-badge">{{ $secNum++ }}</span></td>
                <td><span class="section-title-text">INFORMACIÓN DE CONTROL Y CONDICIONES ELÉCTRICAS</span></td>
            </tr>
        </table>

        {{-- FILA 1: Implementador, Jefe de EESS, Pozo a Tierra, Panel Solar --}}
        <table class="form-grid">
            <tr>
                <td style="width: 25%;">
                    <div class="field-box">
                        <span class="field-label">Monitor / Implementador</span>
                        <span class="field-value">{{ $monitorNombre }}</span>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="field-box">
                        <span class="field-label">Jefe de Establecimiento</span>
                        <span class="field-value">{{ $jefeNombre }}</span>
                    </div>
                </td>
                <td style="width: 25%;">
                    @php $hasPozo = ($acta->pozo_tierra ?? 'NO') === 'SI'; @endphp
                    <div class="field-box" style="{{ $hasPozo ? 'border-color: #86efac; background: #f0fdf4;' : 'border-color: #e2e8f0;' }}">
                        <span class="field-label" style="{{ $hasPozo ? 'color: #166534;' : '' }}">Pozo a Tierra</span>
                        <span class="field-value" style="{{ $hasPozo ? 'color: #166534;' : '' }}">
                            @if($hasPozo)
                                ✓ SÍ ({{ $acta->pozo_tierra_cantidad ?? 1 }} TOTAL | {{ $acta->pozo_tierra_operativos ?? 0 }} OP)
                            @else
                                ✗ NO CUENTA
                            @endif
                        </span>
                    </div>
                </td>
                <td style="width: 25%;">
                    @php $hasSolar = ($acta->panel_solar ?? 'NO') === 'SI'; @endphp
                    <div class="field-box" style="{{ $hasSolar ? 'border-color: #86efac; background: #f0fdf4;' : 'border-color: #e2e8f0;' }}">
                        <span class="field-label" style="{{ $hasSolar ? 'color: #166534;' : '' }}">Panel Solar</span>
                        <span class="field-value" style="{{ $hasSolar ? 'color: #166534;' : '' }}">
                            @if($hasSolar)
                                ✓ SÍ ({{ $acta->panel_solar_cantidad ?? 1 }} TOTAL | {{ $acta->panel_solar_operativos ?? 0 }} OP)
                            @else
                                ✗ NO CUENTA
                            @endif
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        {{-- EQUIPO DE ACOMPAÑAMIENTO TÉCNICO (SI APLICA) --}}
        @if(isset($equipoMonitoreo) && $equipoMonitoreo->count() > 0)
            <div style="margin-top: 5px;">
                <span class="field-label" style="margin-bottom: 2px;">Equipo Técnico de Acompañamiento</span>
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th style="width: 20px; text-align: center;">#</th>
                            <th style="width: 45%;">Apellidos y Nombres</th>
                            <th style="width: 20%;">Documento (DNI)</th>
                            <th>Cargo / Rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($equipoMonitoreo as $idxAcom => $acom)
                            <tr>
                                <td style="text-align: center; font-weight: bold; color: #64748b;">{{ $idxAcom + 1 }}</td>
                                <td style="font-weight: bold; color: #0f172a; text-transform: uppercase;">
                                    {{ trim(($acom->nombres ?? '') . ' ' . ($acom->apellido_paterno ?? '') . ' ' . ($acom->apellido_materno ?? '')) }}
                                </td>
                                <td style="font-weight: bold; color: #4338ca;">{{ $acom->dni ?? $acom->doc ?? '—' }}</td>
                                <td style="text-transform: uppercase;">{{ $acom->cargo ?? 'ACOMPAÑANTE TÉCNICO' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ═══ 2. CONECTIVIDAD Y ACCESO A INTERNET GLOBAL ═══ --}}
    <div class="section-card">
        <table class="section-header-table">
            <tr>
                <td style="width: 20px;"><span class="section-badge">{{ $secNum++ }}</span></td>
                <td><span class="section-title-text">DIAGNÓSTICO GENERAL DE CONECTIVIDAD Y REDES</span></td>
            </tr>
        </table>

        <table class="form-grid">
            <tr>
                <td style="width: 25%;">
                    <div class="kpi-card kpi-card-active">
                        <span class="kpi-title">Operador Predominante (ISP)</span>
                        <div class="kpi-val">{{ $analisisConectividad['mod_operador'] }}</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-card">
                        <span class="kpi-title">Tecnología de Enlace</span>
                        <div class="kpi-val" style="color: #0284c7;">{{ $analisisConectividad['mod_tipo'] }}</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-card">
                        <span class="kpi-title">Velocidad Descarga (Pico / Prom)</span>
                        <div class="kpi-val" style="color: #059669;">
                            {{ $analisisConectividad['max_descarga'] }} Mbps / {{ $analisisConectividad['avg_descarga'] }} Mbps
                        </div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-card">
                        <span class="kpi-title">Velocidad Subida (Pico / Prom)</span>
                        <div class="kpi-val" style="color: #d97706;">
                            {{ $analisisConectividad['max_subida'] }} Mbps / {{ $analisisConectividad['avg_subida'] }} Mbps
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ═══ 3. MATRIZ RESUMEN DE CONSULTORIOS Y SERVICIOS EVALUADOS ═══ --}}
    <div class="section-card">
        <table class="section-header-table">
            <tr>
                <td style="width: 20px;"><span class="section-badge">{{ $secNum++ }}</span></td>
                <td><span class="section-title-text">MATRIZ RESUMEN DE CONSULTORIOS Y SERVICIOS EVALUADOS</span></td>
            </tr>
        </table>

        @if(count($consultoriosLista) > 0)
            <table class="summary-table">
                <thead>
                    <tr>
                        <th style="width: 18px; text-align: center;">#</th>
                        <th style="width: 140px;">Consultorio / Servicio</th>
                        <th style="width: 75px; text-align: center;">Tipo & Piso</th>
                        <th style="width: 80px; text-align: center;">Electricidad</th>
                        <th style="width: 85px; text-align: center;">Puntos de Red</th>
                        <th style="width: 130px;">Conectividad & Vel.</th>
                        <th>Equipos Asociados</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($consultoriosLista as $iCons => $c)
                        @php
                            // Filtrar equipos de este consultorio (o los del físico
                            // vinculado, si este consultorio marcó que los comparte)
                            $eqsModulo = $equiposCol->filter(function($e) use ($c) {
                                return strtolower(trim($e->modulo ?? '')) === strtolower(trim($c['slug_equipos'] ?? $c['slug']));
                            });
                            
                            $resumenEqs = [];
                            if ($eqsModulo->count() > 0) {
                                foreach($eqsModulo as $eqItem) {
                                    $resumenEqs[] = ($eqItem->cantidad ?? 1) . ' ' . ucfirst(strtolower($eqItem->descripcion));
                                }
                            }
                            $textoEquipos = count($resumenEqs) > 0 ? implode(', ', $resumenEqs) : 'Sin equipos';
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: bold; color: #64748b;">{{ $iCons + 1 }}</td>
                            <td>
                                <strong style="color: #0f172a; font-size: 7.5px;">{{ $c['titulo'] }}</strong>
                                @if(!empty($c['servicio']) && $c['servicio'] !== 'GENERAL')
                                    <div style="font-size: 6px; color: #4f46e5; font-weight: bold;">{{ $c['servicio'] }}</div>
                                @endif
                                @if(!empty($c['departamento']))
                                    <div style="font-size: 6px; color: #64748b; font-weight: bold;">{{ $c['departamento'] }}</div>
                                @endif
                            </td>
                            <td style="text-align: center; font-size: 7px;">
                                <span style="font-weight: bold;">{{ $c['tipo'] }}</span>
                                <div style="color: #64748b;">{{ $c['piso'] }}</div>
                            </td>
                            <td style="text-align: center;">
                                @if($c['electricidad'] === 'SI')
                                    <span class="badge badge-si">✓ CUENTA</span>
                                @else
                                    <span class="badge badge-no">✗ NO</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if($c['punto_red'] === 'SI')
                                    <span class="badge badge-si">
                                        ✓ {{ !empty($c['cant_puntos']) ? ($c['cant_puntos'] . ' ' . ((int)$c['cant_puntos'] === 1 ? 'PTO' : 'PTOS')) : 'SÍ' }}
                                    </span>
                                @else
                                    <span class="badge badge-no">✗ NO</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: bold; color: #0f172a; font-size: 7px;">
                                    {{ $c['conectividad'] }}
                                    @if($c['isp'] !== 'N/A' && !empty($c['isp']))
                                        <span style="color: #4f46e5;">({{ $c['isp'] }})</span>
                                    @endif
                                </div>
                                @if(!empty($c['descarga']))
                                    <div style="font-size: 6px; color: #059669; font-weight: bold;">
                                        ↓ {{ $c['descarga'] }} &bull; ↑ {{ $c['subida'] ?: '—' }}
                                    </div>
                                @endif
                            </td>
                            <td style="font-size: 7px; color: #334155;">
                                {{ $textoEquipos }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="background-color: #fafbff; border: 1.5px dashed #cbd5e1; border-radius: 5px; padding: 8px; text-align: center; color: #94a3b8; font-size: 7.5px; font-weight: bold; text-transform: uppercase;">
                No se registraron consultorios específicos en esta acta.
            </div>
        @endif
    </div>

    {{-- ═══ 4. RESUMEN DE RECURSOS HUMANOS Y PROFESIONALES ASISTENCIALES ═══ --}}
    @if($rrhhModulo && count($trabajadores) > 0)
        <div class="section-card">
            <table class="section-header-table">
                <tr>
                    <td style="width: 20px;"><span class="section-badge">{{ $secNum++ }}</span></td>
                    <td>
                        <span class="section-title-text">PADRÓN RESUMIDO DE RECURSOS HUMANOS</span>
                        <span style="float: right; font-size: 7px; font-weight: bold; color: #4f46e5; background: #eef2ff; padding: 1.5px 6px; border-radius: 3px; border: 1px solid #c7d2fe;">
                            TOTAL INTEGRANTES: {{ count($trabajadores) }}
                        </span>
                    </td>
                </tr>
            </table>

            <table class="summary-table">
                <thead>
                    <tr>
                        <th style="width: 18px; text-align: center;">#</th>
                        <th style="width: 160px;">Apellidos y Nombres</th>
                        <th style="width: 75px;">Documento</th>
                        <th style="width: 130px;">Profesión / Especialidad</th>
                        <th style="width: 80px;">Colegiatura / RNE</th>
                        <th style="width: 70px; text-align: center;">SERUMS</th>
                        <th>Contacto (Teléfono / Email)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trabajadores as $idxT => $t)
                        @php
                            $nombreCompleto = trim(($t['apellido_paterno'] ?? '') . ' ' . ($t['apellido_materno'] ?? '') . ' ' . ($t['nombres'] ?? ''));
                            $tipoDoc = strtoupper(trim($t['tipo_doc'] ?? $t['tipo_documento'] ?? 'DNI'));
                            $numDoc = trim($t['doc'] ?? $t['numero_documento'] ?? $t['dni'] ?? '');
                            $serumsVal = strtoupper(trim($t['es_serums'] ?? $t['serums'] ?? 'NO'));
                            $esSerums = ($serumsVal === 'SI' || $serumsVal === '1' || ($t['es_serums'] ?? false) === true);
                            $periodo = trim($t['periodo_serums'] ?? '');
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: bold; color: #64748b;">{{ $idxT + 1 }}</td>
                            <td style="font-weight: bold; color: #0f172a; text-transform: uppercase;">
                                {{ $nombreCompleto }}
                            </td>
                            <td style="font-weight: bold; color: #334155;">
                                <span style="font-size: 6px; color: #64748b;">{{ $tipoDoc }}:</span>
                                <span style="font-size: 7.5px; color: #0f172a;">{{ !empty($numDoc) ? $numDoc : '—' }}</span>
                            </td>
                            <td style="text-transform: uppercase; font-size: 7px; color: #1e293b;">
                                {{ $t['profesion'] ?? 'PERSONAL DE SALUD' }}
                            </td>
                            <td style="font-size: 7px; font-weight: bold; color: #4338ca;">
                                {{ !empty($t['colegiatura']) ? ($t['colegio_profesional'] ? ($t['colegio_profesional'] . ' ' . $t['colegiatura']) : $t['colegiatura']) : '—' }}
                                @if(!empty($t['rne']))
                                    <div style="font-size: 6px; color: #64748b; font-weight: normal;">RNE: {{ $t['rne'] }}</div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if($esSerums)
                                    <span class="badge badge-si" style="font-size: 6px;">
                                        ✓ SÍ {{ !empty($periodo) ? '(' . $periodo . ')' : '' }}
                                    </span>
                                @else
                                    <span class="badge badge-no">NO</span>
                                @endif
                            </td>
                            <td style="font-size: 6.5px; color: #475569;">
                                @if(!empty($t['celular']))
                                    <strong style="color: #0f172a;">{{ $t['celular'] }}</strong>
                                @endif
                                @if(!empty($t['correo']))
                                    <div style="color: #64748b;">{{ strtolower($t['correo']) }}</div>
                                @endif
                                @if(empty($t['celular']) && empty($t['correo']))
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif


    {{-- ═══ 5. CONSOLIDADO DE INVENTARIO TECNOLÓGICO (EQUIPOS E IMPRESORAS) ═══ --}}
    <div class="section-card">
        <table class="section-header-table">
            <tr>
                <td style="width: 20px;"><span class="section-badge">{{ $secNum++ }}</span></td>
                <td>
                    <span class="section-title-text">CONSOLIDADO DE EQUIPOS DE CÓMPUTO E IMPRESORAS</span>
                    <span style="float: right; font-size: 7px; font-weight: bold; color: #059669; background: #ecfdf5; padding: 1.5px 6px; border-radius: 3px; border: 1px solid #a7f3d0;">
                        TOTAL EQUIPOS: {{ $totalEquipos }} &bull; OP: {{ $totalOperativos }} &bull; REG: {{ $totalRegulares }} &bull; INOP: {{ $totalInoperativos }}
                    </span>
                </td>
            </tr>
        </table>

        @if($totalEquipos > 0)
            <table class="summary-table">
                <thead>
                    <tr>
                        <th style="width: 18px; text-align: center;">#</th>
                        <th style="width: 95px;">Módulo / Ubicación</th>
                        <th style="width: 105px;">Descripción del Equipo</th>
                        <th style="width: 28px; text-align: center;">Cant.</th>
                        <th style="width: 60px; text-align: center;">Estado</th>
                        <th style="width: 65px; text-align: center;">Propiedad</th>
                        <th>Especificaciones Técnicas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($equiposCol as $idxE => $eq)
                        @php
                            $est = strtoupper(trim($eq->estado ?? 'OPERATIVO'));
                            $estClass = 'badge-operativo';
                            if ($est === 'REGULAR') $estClass = 'badge-regular';
                            if ($est === 'INOPERATIVO') $estClass = 'badge-inoperativo';
                            
                            $modSlug = strtolower(trim($eq->modulo ?? ''));
                            $consMatch = collect($consultoriosLista)->first(fn($item) => strtolower($item['slug'] ?? '') === $modSlug);
                            $modNombre = $consMatch['titulo'] ?? strtoupper(str_replace(['_', '-'], ' ', preg_replace('/_\d+$/', '', $eq->modulo ?? 'GENERAL')));

                            $descUpper = str_replace(['-', '_'], ' ', strtoupper(trim($eq->descripcion ?? '')));
                            $esComputo = str_contains($descUpper, 'CPU') ||
                                         str_contains($descUpper, 'LAPTOP') ||
                                         str_contains($descUpper, 'ALL IN ONE') ||
                                         str_contains($descUpper, 'AIO') ||
                                         str_contains($descUpper, 'COMPUTADORA') ||
                                         str_contains($descUpper, 'COMPUTADOR') ||
                                         str_contains($descUpper, 'PC');

                            $specs = $eq->especificaciones ?? null;
                            if (is_string($specs)) {
                                $specs = json_decode($specs, true) ?? null;
                            }
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: bold; color: #64748b;">{{ $idxE + 1 }}</td>
                            <td style="font-weight: bold; color: #4338ca; font-size: 7px;">{{ $modNombre }}</td>
                            <td style="font-weight: bold; color: #0f172a;">{{ strtoupper($eq->descripcion) }}</td>
                            <td style="text-align: center; font-weight: bold;">{{ $eq->cantidad ?? 1 }}</td>
                            <td style="text-align: center;">
                                <span class="badge {{ $estClass }}">{{ $est }}</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-propio">{{ strtoupper($eq->propio ?? 'EXCLUSIVO') }}</span>
                            </td>
                            <td style="vertical-align: middle; padding: 3px 5px;">
                                @if($esComputo)
                                    @php
                                        $hasStructuredSpecs = !empty($specs) && is_array($specs) && (
                                            !empty($specs['procesador']) || !empty($specs['ram']) || !empty($specs['disco']) || 
                                            !empty($specs['so']) || !empty($specs['modelo']) || !empty($specs['gpu'])
                                        );
                                    @endphp

                                    @if($hasStructuredSpecs)
                                        <div style="font-size: 6.5px; line-height: 1.25;">
                                            @if(!empty($specs['modelo']) && $specs['modelo'] !== '--')
                                                <div><strong style="color: #4338ca;">MODELO:</strong> <span style="color: #0f172a; font-weight: bold;">{{ strtoupper($specs['modelo']) }}</span></div>
                                            @endif
                                            @if(!empty($specs['procesador']) && $specs['procesador'] !== '--')
                                                <div><strong style="color: #334155;">CPU:</strong> <span style="color: #0f172a;">{{ strtoupper($specs['procesador']) }}</span></div>
                                            @endif
                                            @if((!empty($specs['ram']) && $specs['ram'] !== '--') || (!empty($specs['disco']) && $specs['disco'] !== '--'))
                                                <div>
                                                    @if(!empty($specs['ram']) && $specs['ram'] !== '--')
                                                        <span><strong style="color: #334155;">RAM:</strong> <span style="color: #0f172a; font-weight: bold;">{{ strtoupper($specs['ram']) }}</span></span>
                                                    @endif
                                                    @if(!empty($specs['ram']) && !empty($specs['disco']) && $specs['ram'] !== '--' && $specs['disco'] !== '--')
                                                        &nbsp;&bull;&nbsp;
                                                    @endif
                                                    @if(!empty($specs['disco']) && $specs['disco'] !== '--')
                                                        <span><strong style="color: #334155;">DISCO:</strong> <span style="color: #0f172a; font-weight: bold;">{{ strtoupper($specs['disco']) }}</span></span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if(!empty($specs['so']) && $specs['so'] !== '--')
                                                <div><strong style="color: #64748b;">SO:</strong> <span style="color: #334155;">{{ strtoupper($specs['so']) }}</span></div>
                                            @endif
                                            @if(!empty($specs['gpu']) && $specs['gpu'] !== '--' && !str_contains(strtoupper($specs['gpu']), 'DIRECT3D11') && !str_contains(strtoupper($specs['gpu']), 'INTEGRATED'))
                                                <div><strong style="color: #64748b;">GPU:</strong> <span style="color: #334155;">{{ strtoupper($specs['gpu']) }}</span></div>
                                            @endif
                                        </div>
                                    @elseif(!empty($eq->observacion) && trim($eq->observacion) !== '—' && trim($eq->observacion) !== '-')
                                        <div style="font-size: 6.5px; font-weight: bold; color: #334155; line-height: 1.2;">
                                            {{ strtoupper($eq->observacion) }}
                                        </div>
                                    @else
                                        <span style="font-size: 6.5px; color: #94a3b8; font-style: italic;">Sin especificaciones registradas</span>
                                    @endif
                                @else
                                    <span style="color: #94a3b8; display: block; text-align: center; font-size: 7px;">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="background-color: #fafbff; border: 1.5px dashed #cbd5e1; border-radius: 5px; padding: 8px; text-align: center; color: #94a3b8; font-size: 7.5px; font-weight: bold; text-transform: uppercase;">
                No se registraron equipos de cómputo en este monitoreo.
            </div>
        @endif
    </div>

    {{-- ═══ 6. PANEL FOTOGRÁFICO DE EVIDENCIAS ═══ --}}
    @if(count($fotosConsolidadas) > 0)
        <div class="section-card" style="page-break-inside: avoid;">
            <table class="section-header-table">
                <tr>
                    <td style="width: 20px;"><span class="section-badge">{{ $secNum++ }}</span></td>
                    <td>
                        <span class="section-title-text">PANEL FOTOGRÁFICO DE EVIDENCIAS CONSOLIDADAS</span>
                        <span style="float: right; font-size: 7px; font-weight: bold; color: #4f46e5;">
                            {{ count($fotosConsolidadas) }} {{ count($fotosConsolidadas) === 1 ? 'FOTOGRAFÍA' : 'FOTOGRAFÍAS' }}
                        </span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%; border-collapse: separate; border-spacing: 5px; margin-left: -5px; margin-right: -5px;">
                @foreach(array_chunk($fotosConsolidadas, 3) as $fotoRow)
                    <tr>
                        @foreach($fotoRow as $fItem)
                            <td style="width: 33.33%; vertical-align: top; text-align: center;">
                                <div class="photo-card">
                                    <img src="{{ $fItem['src'] }}" class="photo-img">
                                    <div class="photo-caption">{{ $fItem['titulo'] }}</div>
                                </div>
                            </td>
                        @endforeach
                        @if(count($fotoRow) < 3)
                            @for($k = count($fotoRow); $k < 3; $k++)
                                <td style="width: 33.33%; border: none;"></td>
                            @endfor
                        @endif
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    {{-- ═══ 7. FIRMAS DE CONFORMIDAD ═══ --}}
    <div class="section-card" style="page-break-inside: avoid; margin-top: 6px;">
        <table class="section-header-table">
            <tr>
                <td style="width: 20px;"><span class="section-badge">{{ $secNum++ }}</span></td>
                <td><span class="section-title-text">FIRMAS DE CONFORMIDAD</span></td>
            </tr>
        </table>

        @php
            $listaFirmas = [];
            
            // 1. Monitor / Implementador
            $listaFirmas[] = [
                'nombre' => $monitorNombre,
                'rol'    => 'MONITOR / IMPLEMENTADOR'
            ];

            // 2. Jefe de Establecimiento
            $listaFirmas[] = [
                'nombre' => $jefeNombre,
                'rol'    => 'JEFE DE ESTABLECIMIENTO'
            ];

            // 3. Equipo de Acompañamiento Técnico (si hubiera)
            if (isset($equipoMonitoreo) && $equipoMonitoreo->count() > 0) {
                foreach ($equipoMonitoreo as $acom) {
                    $nomAcom = trim(($acom->nombres ?? '') . ' ' . ($acom->apellido_paterno ?? '') . ' ' . ($acom->apellido_materno ?? ''));
                    if (!empty($nomAcom)) {
                        $listaFirmas[] = [
                            'nombre' => mb_strtoupper($nomAcom, 'UTF-8'),
                            'rol'    => mb_strtoupper($acom->cargo ?? 'ACOMPAÑANTE TÉCNICO', 'UTF-8')
                        ];
                    }
                }
            }

            $firmasFilas = array_chunk($listaFirmas, 2);
        @endphp

        <table class="firmas-table">
            @foreach($firmasFilas as $fila)
                <tr>
                    @foreach($fila as $f)
                        <td style="width: 50%;">
                            <div class="firma-box">
                                <div class="firma-space"></div>
                                <div class="firma-line"></div>
                                <span class="firma-name">{{ $f['nombre'] }}</span>
                                <span class="firma-role">{{ $f['rol'] }}</span>
                            </div>
                        </td>
                    @endforeach
                    @if(count($fila) < 2)
                        <td style="width: 50%; border: none;"></td>
                    @endif
                </tr>
            @endforeach
        </table>
    </div>

    {{-- ═══ FOOTER FIJO INSTITUCIONAL ═══ --}}
    <div class="footer-fixed">
        <div class="footer-inner">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="text-align: left; vertical-align: middle;">
                        <span class="footer-text">
                            Reporte Consolidado de Diagnóstico Situacional IPRESS &bull; EESS: {{ strtoupper($acta->establecimiento->nombre ?? '') }} &bull; Acta #{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }} &bull; {{ date('d/m/Y') }}
                        </span>
                    </td>
                    <td style="text-align: right; width: 40px; vertical-align: middle;">
                        {{-- Espacio reservado para el paginador impreso por script PHP --}}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ═══ SCRIPT DOMPDF: PAGINADOR DINÁMICO (EJ: 1/3, 2/3, 3/3) ═══ --}}
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->get_font("helvetica", "bold");
            $size = 7.5;
            $color = array(0.58, 0.64, 0.72); // #94a3b8
            
            // Altura exacta en el pie de página
            $y = $pdf->get_height() - 24;
            
            // Paginador en la esquina inferior derecha en formato exacto: 1/3, 2/3, 3/3
            $textPag = "{PAGE_NUM}/{PAGE_COUNT}";
            $anchoPag = $fontMetrics->get_text_width("88/88", $font, $size);
            $xRight = $pdf->get_width() - 28 - $anchoPag;
            
            $pdf->page_text($xRight, $y, $textPag, $font, $size, $color);
        }
    </script>

</body>
</html>