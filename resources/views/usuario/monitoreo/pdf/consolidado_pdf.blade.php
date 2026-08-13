<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Diagnóstico Situacional N° {{ ltrim($acta->numero_acta, '0') }}</title>
    <style>
        /* Configuración de Página */
        @page { margin: 1.5cm 1.5cm 2cm 1.5cm; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 9px; 
            color: #334155; 
            line-height: 1.3; 
            margin: 0;
        }

        /* Encabezado Principal */
        .header { 
            text-align: center; 
            margin-bottom: 15px; 
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
        }
        .header h1 { 
            margin: 0; 
            font-size: 14px; 
            color: #0f172a; 
            text-transform: uppercase; 
        }
        .header-sub {
            font-size: 10px;
            margin-top: 4px;
            color: #1e293b;
        }

        /* Títulos de Sección Principales */
        .section-header { 
            background-color: #e2e8f0; 
            padding: 5px 10px; 
            font-weight: bold; 
            font-size: 10px;
            color: #0f172a;
            margin: 15px 0 8px 0; 
            text-transform: uppercase;
        }

        /* Contenedor de cada Módulo */
        .modulo-container {
            border: 1px solid #cbd5e1;
            margin-bottom: 12px;
            border-radius: 2px;
        }
        .modulo-title {
            background-color: #f1f5f9;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 10px;
            color: #0f172a;
            border-bottom: 1px solid #cbd5e1;
            text-transform: uppercase;
        }
        .sub-section {
            background-color: #f8fafc;
            font-size: 8px;
            font-weight: bold;
            color: #475569;
            padding: 3px 8px;
            border-bottom: 1px solid #e2e8f0;
            border-top: 1px solid #e2e8f0;
        }

        /* Tablas de Datos (Compactas) */
        table.data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 0;
        }
        table.data-table th, table.data-table td { 
            border: 1px solid #e2e8f0; 
            padding: 4px 6px; 
            word-wrap: break-word;
            vertical-align: middle;
        }
        table.data-table th {
            background-color: #f1f5f9;
            text-align: left;
            font-size: 8px;
            color: #475569;
            text-transform: uppercase;
        }
        .bg-label { 
            background-color: #f8fafc; 
            font-weight: bold; 
            color: #475569;
            font-size: 8px;
        }
        .uppercase { text-transform: uppercase; font-size: 8px; }

        /* --- EVIDENCIA FOTOGRÁFICA --- */
        .no-evidence-box {
            border: 2px dashed #cbd5e1;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            color: #64748b;
            font-style: italic;
            background-color: #f8fafc;
            margin-top: 10px;
        }

        /* --- ESTILO UNIFICADO PARA FOTOS (PREMIUM) --- */
        .photo-img, .foto, .preview-image, .photo-box img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }
        
        .foto-caption {
            font-size: 7px;
            font-weight: bold;
            color: #1e293b;
            margin-top: 4px;
            text-transform: uppercase;
            text-align: center;
        }

        /* Firmas - DISEÑO ACTUALIZADO SEGÚN IMAGEN */
        .firmas-grid {
            width: 100%;
            margin-top: 15px;
            text-align: center;
            page-break-inside: avoid;
        }
        .firma-card { 
            width: 31%; 
            display: inline-block; 
            vertical-align: top; 
            margin: 5px 1%;
            background-color: #ffffff;
            border: 1px solid #cbd5e1; /* Borde gris/celeste claro del marco */
            border-radius: 6px; /* Esquinas redondeadas como en la imagen */
            box-sizing: border-box;
        }
        .firma-espacio {
            height: 50px; /* Espacio en blanco para firmar encima de la línea */
            width: 100%;
        }
        .firma-bottom {
            padding: 0 10px 12px 10px; /* Espaciado interno inferior */
        }
        .linea-firma { 
            border-top: 1px solid #94a3b8; /* Línea recta sólida, no punteada */
            margin: 0 auto 6px auto; 
            width: 95%; /* La línea no toca los bordes de la caja */
        }
        .nombre-firma { 
            font-size: 8px; 
            font-weight: bold; 
            color: #0f172a; /* Azul muy oscuro/negro */
            display: block;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .cargo-firma { 
            font-size: 7px; 
            color: #64748b; /* Gris azulado más claro */
            display: block;
            text-transform: uppercase;
        }

        /* Footer */
        #footer {
            position: fixed;
            bottom: -1.5cm;
            left: 0;
            right: 0;
            height: 40px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
        .footer-text { font-size: 8px; color: #94a3b8; }
        .page-number:before { content: "Página " counter(page); }
        .no-break { page-break-inside: avoid; }
    </style>
</head>
<body>

    <div id="footer">
        <div class="footer-text">
            Acta de Diagnóstico Situacional IPRESS NO ESPECIALIZADAS N° {{ ltrim($acta->numero_acta, '0') }} | <span class="page-number"></span> 
        </div>
    </div>

    <div class="header">
        <h1>REPORTE CONSOLIDADO DE DIAGNÓSTICO SITUACIONAL IPRESS</h1>
        <div class="header-sub">
            <strong>Establecimiento:</strong> {{ strtoupper($acta->establecimiento->nombre ?? 'ESTABLECIMIENTO NO REGISTRADO') }} 
            &nbsp;|&nbsp; 
            <strong>Acta N°:</strong> {{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}
        </div>
    </div>

    <div class="section-header" style="margin-top: 0;">1. INFORMACIÓN DE CONTROL</div>
    <table class="data-table">
        <tr>
            <td class="bg-label" style="width: 20%;">FECHA DE MONITOREO:</td>
            <td style="width: 30%;">{{ \Carbon\Carbon::parse($acta->fecha)->format('d/m/Y') }}</td>
            <td class="bg-label" style="width: 20%;">MONITOR / IMPLEMENTADOR:</td>
            <td class="uppercase" style="width: 30%;">{{ $monitor['nombre'] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="bg-label">JEFE DEL ESTABLECIMIENTO:</td>
            <td class="uppercase">{{ $jefe['nombre'] ?? 'N/A' }}</td>
            <td class="bg-label">POZO A TIERRA:</td>
            <td class="uppercase font-bold" style="color: #3730a3;">
                {{ ($acta->pozo_tierra ?? 'NO') === 'SI' ? ('SÍ (' . ($acta->pozo_tierra_cantidad ?? 1) . ' POZO' . (($acta->pozo_tierra_cantidad ?? 1) > 1 ? 'S' : '') . ')') : 'NO' }}
            </td>
        </tr>
    </table>

    @if(isset($equipoMonitoreo) && $equipoMonitoreo->count() > 0)
        <div class="sub-section" style="border-top: none; margin-top: 5px;">EQUIPO DE ACOMPAÑAMIENTO</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 45%">NOMBRE COMPLETO</th>
                    <th style="width: 20%">DNI</th>
                    <th style="width: 35%">CARGO</th>
                </tr>
            </thead>
            <tbody>
                @foreach($equipoMonitoreo as $acom)
                <tr>
                    <td class="uppercase">{{ trim(($acom->nombres ?? '') . ' ' . ($acom->apellido_paterno ?? '') . ' ' . ($acom->apellido_materno ?? '')) }}</td>
                    <td class="uppercase">{{ $acom->dni ?? $acom->doc ?? '-' }}</td>
                    <td class="uppercase">{{ $acom->cargo ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="section-header">2. ANÁLISIS GENERAL DE CONECTIVIDAD DEL ESTABLECIMIENTO</div>
    <table class="data-table">
        <tr>
            <td class="bg-label" style="width: 25%;">VEL. DESCARGA (PICO / PROM):</td>
            <td class="uppercase" style="width: 25%;">{{ $analisisConectividad['max_descarga'] }} Mbps / {{ $analisisConectividad['avg_descarga'] }} Mbps</td>
            <td class="bg-label" style="width: 25%;">VEL. SUBIDA (PICO / PROM):</td>
            <td class="uppercase" style="width: 25%;">{{ $analisisConectividad['max_subida'] }} Mbps / {{ $analisisConectividad['avg_subida'] }} Mbps</td>
        </tr>
        <tr>
            <td class="bg-label">OPERADOR PREDOMINANTE:</td>
            <td class="uppercase">{{ $analisisConectividad['mod_operador'] }}</td>
            <td class="bg-label">TECNOLOGÍA PREDOMINANTE:</td>
            <td class="uppercase">{{ $analisisConectividad['mod_tipo'] }}</td>
        </tr>
    </table>

    <div class="section-header">3. RESUMEN DE HALLAZGOS POR MÓDULOS</div>

    @php
        $ordenEstricto = [
            'gestion_administrativa' => 'GESTION ADMINISTRATIVA',
            'citas'                  => 'CITAS',
            'triaje'                 => 'TRIAJE',
            'consulta_medicina'      => 'CONSULTA EXTERNA: MEDICINA',
            'consulta_odontologia'   => 'CONSULTA EXTERNA: ODONTOLOGIA',
            'odontologia'            => 'CONSULTA EXTERNA: ODONTOLOGIA', 
            'consulta_nutricion'     => 'CONSULTA EXTERNA: NUTRICION',
            'nutricion'              => 'CONSULTA EXTERNA: NUTRICION', 
            'consulta_psicologia'    => 'CONSULTA EXTERNA: PSICOLOGIA',
            'psicologia'             => 'CONSULTA EXTERNA: PSICOLOGIA', 
            'cred'                   => 'CRED',
            'inmunizaciones'         => 'INMUNIZACIONES',
            'atencion_prenatal'      => 'ATENCION PRENATAL',
            'prenatal'               => 'ATENCION PRENATAL', 
            'planificacion_familiar' => 'PLANIFICACION FAMILIAR',
            'planificacion'          => 'PLANIFICACION FAMILIAR', 
            'parto'                  => 'PARTO',
            'puerperio'              => 'PUERPERIO',
            'fua_electronico'        => 'FUA ELECTRONICO',
            'farmacia'               => 'FARMACIA',
            'referencias'            => 'REFERENCIAS Y CONTRAREFERENCIAS',
            'refcon'                 => 'REFERENCIAS Y CONTRAREFERENCIAS', 
            'laboratorio'            => 'LABORATORIO',
            'urgencias'              => 'URGENCIAS Y EMERGENCIAS',
            'infraestructura_2d'     => 'INFRAESTRUCTURA Y CROQUIS 2D'
        ];
        
        $impresos = []; 
        $contadorModulo = 1;
        $hiddenKeys = ['id', 'acta_id', 'foto_evidencia', 'fotos_evidencia', 'comentarios', 'observaciones', 'password', 'token', 'created_at', 'updated_at', '_token'];
    @endphp

    @foreach($ordenEstricto as $nombreTecnico => $tituloPublico)
        @php 
            $mod = collect($modulos)->first(function($item) use ($nombreTecnico) {
                return strtolower($item->modulo_nombre) === strtolower($nombreTecnico);
            });
        @endphp

        @if($mod && !in_array($mod->id, $impresos))
            @php 
                $impresos[] = $mod->id; 
                $rawCont = $mod->contenido;
                $cont = is_array($rawCont) ? $rawCont : (is_string($rawCont) ? json_decode($rawCont, true) : []); 
            @endphp
            
            <div class="modulo-container">
                <div class="modulo-title">MÓDULO {{ $contadorModulo }}: {{ $tituloPublico }}</div>
                
                @if(is_array($cont))
                    @php
                        $filasUnificadas = [];
                        foreach($cont as $k => $v) {
                            if(in_array($k, $hiddenKeys)) continue;
                            if(is_array($v)) {
                                if(!isset($v[0])) {
                                    foreach($v as $subK => $subV) {
                                        if(!is_array($subV) && !in_array($subK, $hiddenKeys) && $subV !== null && trim($subV) !== '') {
                                            $filasUnificadas[$k . ' ' . $subK] = $subV;
                                        }
                                    }
                                }
                            } else {
                                if($v !== null && trim($v) !== '') {
                                    $filasUnificadas[$k] = $v;
                                }
                            }
                        }

                        // Juntar las unidades de medida con los valores de velocidad para que no salgan en filas separadas
                        $v_keys = [
                            ['val' => 'conectividad descarga', 'uni' => 'conectividad descarga_unidad', 'old_uni' => 'conectividad unidad'],
                            ['val' => 'conectividad subida', 'uni' => 'conectividad subida_unidad', 'old_uni' => 'conectividad unidad'],
                            ['val' => 'conectividad velocidad_descarga', 'uni' => 'conectividad velocidad_descarga_unidad', 'old_uni' => 'conectividad velocidad_internet_unidad'],
                            ['val' => 'conectividad velocidad_subida', 'uni' => 'conectividad velocidad_subida_unidad', 'old_uni' => 'conectividad velocidad_internet_unidad'],
                            ['val' => 'velocidad_descarga', 'uni' => 'velocidad_descarga_unidad', 'old_uni' => 'velocidad_internet_unidad'],
                            ['val' => 'velocidad_subida', 'uni' => 'velocidad_subida_unidad', 'old_uni' => 'velocidad_internet_unidad']
                        ];
                        
                        foreach ($v_keys as $vk) {
                            if (isset($filasUnificadas[$vk['val']])) {
                                $uni = $filasUnificadas[$vk['uni']] ?? $filasUnificadas[$vk['old_uni']] ?? 'Mbps';
                                $filasUnificadas[$vk['val']] .= ' ' . $uni;
                            }
                            unset($filasUnificadas[$vk['uni']]);
                            unset($filasUnificadas[$vk['old_uni']]);
                        }

                        // Categorizador Dinámico
                        $grupoConsultorio = [];
                        $grupoProfesional = [];
                        $grupoDoc = [];
                        $grupoSoporte = [];
                        $grupoOtros = [];

                        foreach($filasUnificadas as $label => $val) {
                            $lblLower = strtolower($label);
                            
                            if(str_contains($lblLower, 'sihce') || str_contains($lblLower, 'dj') || str_contains($lblLower, 'confidencialidad') || str_contains($lblLower, 'dni fisico') || str_contains($lblLower, 'tipo dni') || str_contains($lblLower, 'dnie') || str_contains($lblLower, 'digital')) {
                                $grupoDoc[$label] = $val;
                            } elseif(str_contains($lblLower, 'fecha') || str_contains($lblLower, 'turno') || str_contains($lblLower, 'consultorio') || str_contains($lblLower, 'ventanilla') || str_contains($lblLower, 'horario')) {
                                $grupoConsultorio[$label] = $val;
                            } elseif(str_contains($lblLower, 'profesional') || str_contains($lblLower, 'personal') || str_contains($lblLower, 'rrhh') || str_contains($lblLower, 'cargo') || str_contains($lblLower, 'email') || str_contains($lblLower, 'telefono') || str_contains($lblLower, 'celular') || str_contains($lblLower, 'contacto') || str_contains($lblLower, 'rol') || str_contains($lblLower, 'especialidad')) {
                                $grupoProfesional[$label] = $val;
                            } elseif(str_contains($lblLower, 'capacitacion') || str_contains($lblLower, 'comunica') || str_contains($lblLower, 'soporte') || str_contains($lblLower, 'conectividad') || str_contains($lblLower, 'operador') || str_contains($lblLower, 'wifi')) {
                                $grupoSoporte[$label] = $val;
                            } else {
                                $grupoOtros[$label] = $val;
                            }
                        }

                        // Helper para imprimir chunks de 2 columnas
                        $renderChunkTable = function($grupoArray) {
                            if(count($grupoArray) == 0) return '';
                            $html = '<table class="data-table">';
                            foreach(array_chunk($grupoArray, 2, true) as $chunk) {
                                $html .= '<tr>';
                                $i = 0;
                                foreach($chunk as $l => $v) {
                                    $valStr = is_bool($v) ? ($v ? 'SI' : 'NO') : $v;
                                    $cleanLabel = strtoupper(str_replace(['_', 'inst'], [' ', 'entidad'], $l));
                                    $html .= '<td class="bg-label" style="width: 25%;">'.$cleanLabel.':</td>';
                                    $html .= '<td class="uppercase" style="width: 25%;">'.$valStr.'</td>';
                                    $i++;
                                }
                                if($i == 1) { // Rellenar espacio vacío si es impar
                                    $html .= '<td class="bg-label" style="width: 25%;"></td><td style="width: 25%;"></td>';
                                }
                                $html .= '</tr>';
                            }
                            $html .= '</table>';
                            return $html;
                        };
                    @endphp

                    {{-- Bloque Consultorio y Profesional --}}
                    @if(count($grupoConsultorio) > 0 || count($grupoProfesional) > 0)
                        <table style="width: 100%; border-collapse: collapse; border: none; padding: 0;">
                            <tr>
                                @if(count($grupoConsultorio) > 0)
                                <td style="width: 50%; padding: 0; vertical-align: top; border-right: 1px solid #e2e8f0;">
                                    <div class="sub-section" style="border-top: none;">DETALLE DEL CONSULTORIO</div>
                                    <table class="data-table" style="border: none;">
                                        @foreach($grupoConsultorio as $l => $v)
                                        <tr>
                                            <td class="bg-label" style="border-left:none;">{{ strtoupper(str_replace('_', ' ', $l)) }}:</td>
                                            <td class="uppercase" style="border-right:none;">{{ is_bool($v) ? ($v ? 'SI' : 'NO') : $v }}</td>
                                        </tr>
                                        @endforeach
                                    </table>
                                </td>
                                @endif
                                
                                @if(count($grupoProfesional) > 0)
                                <td style="{{ count($grupoConsultorio) > 0 ? 'width: 50%;' : 'width: 100%;' }} padding: 0; vertical-align: top;">
                                    <div class="sub-section" style="border-top: none;">DATOS DEL PROFESIONAL</div>
                                    <table class="data-table" style="border: none;">
                                        @foreach($grupoProfesional as $l => $v)
                                        <tr>
                                            <td class="bg-label" style="border-left:none;">{{ strtoupper(str_replace('_', ' ', $l)) }}:</td>
                                            <td class="uppercase" style="border-right:none;">{{ is_bool($v) ? ($v ? 'SI' : 'NO') : $v }}</td>
                                        </tr>
                                        @endforeach
                                    </table>
                                </td>
                                @endif
                            </tr>
                        </table>
                    @endif

                    {{-- Documentación y Firma --}}
                    @if(count($grupoDoc) > 0)
                        <div class="sub-section">DOCUMENTACIÓN Y FIRMA DIGITAL</div>
                        {!! $renderChunkTable($grupoDoc) !!}
                    @endif

                    {{-- Capacitación y Soporte --}}
                    @if(count($grupoSoporte) > 0)
                        <div class="sub-section">CAPACITACIÓN Y SOPORTE</div>
                        {!! $renderChunkTable($grupoSoporte) !!}
                    @endif

                    {{-- Otros Datos --}}
                    @if(count($grupoOtros) > 0)
                        <div class="sub-section">DATOS ADICIONALES DEL MÓDULO</div>
                        {!! $renderChunkTable($grupoOtros) !!}
                    @endif

                    {{-- Fotos Dinámicas del Módulo --}}
                    @php
                        $fotosModulo = [];
                        
                        $extraerEvidencias = function($val) {
                            if (empty($val)) return [];
                            if (is_array($val)) return $val;
                            if (is_string($val)) {
                                $decoded = json_decode($val, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    return is_array($decoded) ? $decoded : [$decoded];
                                }
                                return [$val]; // Era un string plano sin formato JSON
                            }
                            return [$val];
                        };

                        // 1. Array de posibles evidencias dentro de $cont
                        $evidenciasCont = [];
                        if (!empty($cont['fotos_evidencia'])) {
                            $evidenciasCont = $extraerEvidencias($cont['fotos_evidencia']);
                        } elseif (!empty($cont['foto_evidencia'])) {
                            $evidenciasCont = $extraerEvidencias($cont['foto_evidencia']);
                        }

                        if (!empty($evidenciasCont) && is_array($evidenciasCont)) {
                            foreach($evidenciasCont as $ruta) {
                                if (!empty($ruta)) {
                                    $isFullUrl = str_starts_with($ruta, 'http');
                                    $realPath = $isFullUrl ? $ruta : public_path('storage/' . ltrim($ruta, '/'));
                                    $fallbackPath = $isFullUrl ? $ruta : storage_path('app/public/' . ltrim($ruta, '/'));
                                    
                                    if($isFullUrl || file_exists($realPath)) {
                                        $fotosModulo[] = $realPath;
                                    } elseif (file_exists($fallbackPath)) {
                                        $fotosModulo[] = $fallbackPath;
                                    }
                                }
                            }
                        }

                        // 2. Revisar foto_1 y foto_2 (Pueden estar en $cont o directamente en $mod)
                        if (empty($fotosModulo)) {
                            foreach(['foto_1', 'foto_2'] as $fKey) {
                                $fPath = $cont[$fKey] ?? ($mod->$fKey ?? null);
                                if (!empty($fPath)) {
                                    $fIsFull = str_starts_with($fPath, 'http');
                                    $fRealPath = $fIsFull ? $fPath : public_path('storage/' . ltrim($fPath, '/'));
                                    $fallbackPath = $fIsFull ? $fPath : storage_path('app/public/' . ltrim($fPath, '/'));
                                    
                                    if($fIsFull || file_exists($fRealPath)) {
                                        $fotosModulo[] = $fRealPath;
                                    } elseif (file_exists($fallbackPath)) {
                                        $fotosModulo[] = $fallbackPath;
                                    }
                                }
                            }
                        }
                        
                        $cantidadMod = count($fotosModulo);
                    @endphp

                    @if ($cantidadMod > 0)
                        <div class="sub-section">FOTOGRAFÍAS</div>
                        @if ($cantidadMod == 1)
                        <div style="width: 100%; text-align: center; margin-top: 5px;">
                            <div style="display: inline-block; border: 1px solid #e2e8f0; padding: 5px; background: #fff; border-radius: 10px;">
                                <img src="{{ $fotosModulo[0] }}" style="width: 100%; height: 250px; object-fit: cover; border-radius: 8px;">
                            </div>
                        </div>
                        @else
                        <table style="width: 100%; border: none; margin-top: 5px;">
                            <tr>
                                @foreach ($fotosModulo as $index => $fotoUrl)
                                    @if ($index > 0 && $index % 2 == 0)
                            </tr>
                            <tr>
                                    @endif

                                    <td style="border: none; padding: 5px; text-align: center; width: 50%;">
                                        <div style="border: 1px solid #e2e8f0; padding: 4px; background: #fff; border-radius: 10px;">
                                            <img src="{{ $fotoUrl }}" style="width: 100%; height: 250px; object-fit: cover; border-radius: 8px;">
                                        </div>
                                    </td>
                                @endforeach

                                @if ($cantidadMod % 2 != 0)
                                    <td style="border: none;"></td>
                                @endif
                            </tr>
                        </table>
                        @endif
                    @endif

                @else
                    <div style="padding: 10px; font-style: italic; font-size: 8px;">Sin información detallada registrada.</div>
                @endif
            </div>
            @php $contadorModulo++; @endphp
        @endif
    @endforeach

    <div class="section-header">3. DETALLE DE EQUIPAMIENTO POR MÓDULO</div>
    
    @if($equipos && $equipos->count() > 0)
        <table class="data-table" style="margin-bottom: 15px;">
            <thead>
                <tr>
                    <th width="5%" style="text-align: center;">N°</th>
                    <th width="15%">MÓDULO</th>
                    <th width="15%">SERIE/CÓDIGO</th>
                    <th width="5%" style="text-align: center;">CANT.</th>
                    <th width="25%">DESCRIPCIÓN DEL EQUIPO</th>
                    <th width="10%" style="text-align: center;">ESTADO</th>
                    <th width="10%">PROPIEDAD</th>
                    <th width="15%">OBSERVACIÓN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($equipos as $index => $eq)
                    <tr class="uppercase">
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ strtoupper(str_replace('_', ' ', $eq->modulo)) }}</td>
                        <td style="font-family: monospace;">{{ $eq->nro_serie ?? '---' }}</td>
                        <td style="text-align: center;">{{ $eq->cantidad ?? '1' }}</td>
                        <td>{{ $eq->descripcion }}</td>
                        <td style="text-align: center;">{{ $eq->estado ?? 'N/A' }}</td>
                        <td>{{ $eq->propio ?? '---' }}</td>
                        <td>{{ $eq->observacion ?? '---' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="padding-left: 10px; font-style: italic; color: #64748b; font-size: 9px;">No se registró equipamiento tecnológico en este monitoreo.</p>
    @endif

    <div class="no-break">
        <div class="section-header">4. PANEL FOTOGRÁFICO</div>
        @php
            $fotosCabecera = [];
            if($acta->foto1 && file_exists(public_path('storage/' . $acta->foto1))) {
                $fotosCabecera[] = ['url' => public_path('storage/' . $acta->foto1), 'caption' => 'FOTO 01 - REGISTRO DE MONITOREO'];
            }
            if($acta->foto2 && file_exists(public_path('storage/' . $acta->foto2))) {
                $fotosCabecera[] = ['url' => public_path('storage/' . $acta->foto2), 'caption' => 'FOTO 02 - REGISTRO DE MONITOREO'];
            }
            $cantidadCabecera = count($fotosCabecera);
        @endphp

        @if ($cantidadCabecera > 0)
            @if ($cantidadCabecera == 1)
            <div style="width: 100%; text-align: center; margin-top: 15px;">
                <div style="display: inline-block; border: 1px solid #e2e8f0; padding: 5px; background: #fff; border-radius: 10px;">
                    <img src="{{ $fotosCabecera[0]['url'] }}" style="width: 100%; height: 250px; object-fit: cover; border-radius: 8px;">
                    <div class="foto-caption">{{ $fotosCabecera[0]['caption'] }}</div>
                </div>
            </div>
            @else
            <table style="width: 100%; border: none; margin-top: 10px;">
                <tr>
                    @foreach ($fotosCabecera as $index => $fotoData)
                        <td style="border: none; padding: 5px; text-align: center; width: 50%;">
                            <div style="border: 1px solid #e2e8f0; padding: 4px; background: #fff; border-radius: 10px;">
                                <img src="{{ $fotoData['url'] }}" style="width: 100%; height: 250px; object-fit: cover; border-radius: 8px;">
                                <div class="foto-caption">{{ $fotoData['caption'] }}</div>
                            </div>
                        </td>
                    @endforeach
                </tr>
            </table>
            @endif
        @else
            <div class="no-evidence-box">NO SE ADJUNTARON FOTOGRAFÍAS EN ESTA ACTA.</div>
        @endif
    </div>

    <div class="no-break">
        <div class="section-header">5. FIRMAS DE CONFORMIDAD</div>
        <div class="firmas-grid">
            
            <div class="firma-card">
                <div class="firma-espacio"></div>
                <div class="firma-bottom">
                    <div class="linea-firma"></div>
                    <span class="nombre-firma">{{ $monitor['nombre'] ?? 'MONITOR' }}</span>
                    <span class="cargo-firma">IMPLEMENTADOR</span>
                </div>
            </div>

            <div class="firma-card">
                <div class="firma-espacio"></div>
                <div class="firma-bottom">
                    <div class="linea-firma"></div>
                    <span class="nombre-firma">{{ $jefe['nombre'] ?? 'JEFE DE ESTABLECIMIENTO' }}</span>
                    <span class="cargo-firma">JEFE DEL ESTABLECIMIENTO</span>
                </div>
            </div>

            @foreach($equipoMonitoreo as $miembro)
                <div class="firma-card">
                    <div class="firma-espacio"></div>
                    <div class="firma-bottom">
                        <div class="linea-firma"></div>
                        <span class="nombre-firma">{{ strtoupper(trim(($miembro->apellido_paterno ?? '') . ' ' . ($miembro->apellido_materno ?? ''). ' ' . ($miembro->nombres ?? ''))) }}</span>
                        <span class="cargo-firma">{{ strtoupper($miembro->institucion ?? 'ACOMPAÑANTE TÉCNICO') }}</span>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</body>
</html>