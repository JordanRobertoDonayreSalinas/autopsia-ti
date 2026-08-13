<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Monitoreo N° {{ ltrim($acta->numero_acta, '0') }}</title>
    <style>
        /* Configuración de Página */
        @page { margin: 1.5cm 2cm 2.5cm 2cm; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 10px; 
            color: #334155; 
            line-height: 1.5; 
            margin: 0;
        }

        /* Encabezado */
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
        }
        .header h1 { 
            margin: 0; 
            font-size: 16px; 
            color: #0f172a; 
            text-transform: uppercase; 
        }
        .header .establishment {
            margin-top: 5px;
            font-size: 12px;
            color: #1e293b;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Títulos de Sección */
        .section-header { 
            background-color: #f8fafc; 
            padding: 6px 12px; 
            font-weight: bold; 
            font-size: 10px;
            color: #1e293b;
            border-left: 4px solid #0f172a; 
            margin: 15px 0 10px 0; 
            text-transform: uppercase;
        }

        /* Tablas */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 10px; 
        }
        th, td { 
            border: 1px solid #e2e8f0; 
            padding: 6px 10px; 
            word-wrap: break-word;
        }
        th {
            background-color: #f1f5f9;
            text-align: left;
            font-size: 8px;
            color: #475569;
            text-transform: uppercase;
        }
        .bg-label { 
            background-color: #f8fafc; 
            font-weight: bold; 
            width: 40%; 
        }
        .uppercase { text-transform: uppercase; }

        /* Fotos */
        .foto-grid {
            width: 100%;
            margin: 20px 0;
            text-align: center;
        }
        .foto-wrapper {
            width: 100%; 
            height: 250px; 
            display: block;
            margin: 8px 0;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            border-radius: 10px;
            overflow: hidden;
            vertical-align: top;
        }
        .foto-wrapper img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }
        .foto-caption {
            font-size: 8px;
            font-weight: bold;
            color: #1e293b;
            margin-top: 5px;
            text-transform: uppercase;
        }

        /* Firmas */
        .firmas-grid {
            width: 100%;
            margin-top: 20px;
            text-align: center;
        }
        .firma-card { 
            width: 30%; 
            display: inline-block; 
            vertical-align: top; 
            margin: 5px;
            border: 1px solid #cbd5e1; 
            background-color: #ffffff;
            border-radius: 4px;
            padding: 15px 5px 10px 5px;
        }
        .linea-firma { 
            border-top: 1px solid #94a3b8; 
            margin: 40px auto 8px auto; 
            width: 85%; 
        }
        .nombre-firma { 
            font-size: 8px; 
            font-weight: bold; 
            color: #0f172a;
            display: block;
            text-transform: uppercase;
        }
        .cargo-firma { 
            font-size: 7px; 
            color: #64748b; 
            display: block;
            text-transform: uppercase;
        }

        /* Footer */
        #footer {
            position: fixed;
            bottom: -1cm;
            left: 0;
            right: 0;
            height: 50px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .footer-text { font-size: 8px; color: #94a3b8; }
        .page-number:before { content: "Página " counter(page); }
        .no-break { page-break-inside: avoid; }
    </style>
</head>
<body>

    <div id="footer">
        <div class="footer-text">
            Acta de Monitoreo IPRESS NO ESPECIALIZADAS N° {{ ltrim($acta->numero_acta, '0') }}<br>
            <span class="page-number"></span> 
        </div>
    </div>

    <div class="header">
        <h1>Acta de Monitoreo IPRESS NO ESPECIALIZADAS N° {{ ltrim($acta->numero_acta, '0') }}</h1>
        <div class="establishment">{{ strtoupper($acta->establecimiento->nombre ?? 'ESTABLECIMIENTO NO REGISTRADO') }}</div>
    </div>

    <div class="section-header">1. Información de Control</div>
    <table>
        <tr>
            <td class="bg-label">Fecha de Monitoreo:</td>
            <td>{{ \Carbon\Carbon::parse($acta->fecha)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="bg-label">Monitor / Implementador:</td>
            <td class="uppercase">{{ $monitor['nombre'] ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Jefe del Establecimiento:</td>
            <td class="uppercase">{{ $jefe['nombre'] ?? 'N/A' }}</td>
        </tr>
    </table>

    {{-- Equipo de Acompañamiento --}}
    @if(isset($equipoMonitoreo) && $equipoMonitoreo->count() > 0)
        <div style="font-weight: bold; font-size: 10px; margin: 10px 0 5px 0; color: #1e293b; padding-left: 5px;">EQUIPO DE ACOMPAÑAMIENTO:</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 50%">NOMBRE COMPLETO</th>
                    <th style="width: 25%">DNI</th>
                    <th style="width: 25%">CARGO</th>
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

    <div class="section-header">2. RESUMEN DE HALLAZGOS POR MÓDULOS</div>

    @php
        // Lista ESTRICTA de módulos permitidos. Cualquier cosa que no esté aquí, SE IGNORA.
        $ordenEstricto = [
            'gestion_administrativa' => 'GESTION ADMINISTRATIVA',
            'citas'                  => 'CITAS',
            'triaje'                 => 'TRIAJE',
            'consulta_medicina'      => 'CONSULTA EXTERNA: MEDICINA',
            'consulta_odontologia'   => 'CONSULTA EXTERNA: ODONTOLOGIA',
            'odontologia'            => 'CONSULTA EXTERNA: ODONTOLOGIA', // Fallback
            'consulta_nutricion'     => 'CONSULTA EXTERNA: NUTRICION',
            'nutricion'              => 'CONSULTA EXTERNA: NUTRICION', // Fallback
            'consulta_psicologia'    => 'CONSULTA EXTERNA: PSICOLOGIA',
            'psicologia'             => 'CONSULTA EXTERNA: PSICOLOGIA', // Fallback
            'cred'                   => 'CRED',
            'inmunizaciones'         => 'INMUNIZACIONES',
            'atencion_prenatal'      => 'ATENCION PRENATAL',
            'prenatal'               => 'ATENCION PRENATAL', // Fallback
            'planificacion_familiar' => 'PLANIFICACION FAMILIAR',
            'planificacion'          => 'PLANIFICACION FAMILIAR', // Fallback
            'parto'                  => 'PARTO',
            'puerperio'              => 'PUERPERIO',
            'fua_electronico'        => 'FUA ELECTRONICO',
            'farmacia'               => 'FARMACIA',
            'referencias'            => 'REFERENCIAS Y CONTRAREFERENCIAS',
            'refcon'                 => 'REFERENCIAS Y CONTRAREFERENCIAS', // Fallback
            'laboratorio'            => 'LABORATORIO',
            'urgencias'              => 'URGENCIAS Y EMERGENCIAS'
        ];
        
        $impresos = []; // Control para no repetir módulos
        $contadorModulo = 1;
        $hiddenKeys = ['id', 'acta_id', 'foto_evidencia', 'fotos_evidencia', 'comentarios', 'observaciones', 'password', 'token', 'created_at', 'updated_at', '_token'];
    @endphp

    @foreach($ordenEstricto as $nombreTecnico => $tituloPublico)
        @php 
            $mod = $modulos->first(function($item) use ($nombreTecnico) {
                return strtolower($item->modulo_nombre) === strtolower($nombreTecnico);
            });
        @endphp

        {{-- IMPRIMIR SOLO SI EXISTE, NO HA SIDO IMPRESO Y TIENE CONTENIDO REAL --}}
        @if($mod && !in_array($mod->id, $impresos))
            @php 
                $impresos[] = $mod->id; 
                $cont = is_string($mod->contenido) ? json_decode($mod->contenido, true) : $mod->contenido; 
            @endphp
            
            <div class="no-break">
                <div style="background-color: #f1f5f9; padding: 4px 10px; font-weight: bold; font-size: 9px; margin-bottom: 2px; border: 1px solid #e2e8f0; text-transform: uppercase;">
                    MÓDULO {{ $contadorModulo }}: {{ $tituloPublico }}
                </div>
                
                @if(is_array($cont))
                    @php
                        $filasUnificadas = [];
                        foreach($cont as $k => $v) {
                            if(in_array($k, $hiddenKeys)) continue;
                            if(is_array($v)) {
                                if(!isset($v[0])) { // Es grupo asociativo, lo aplanamos
                                    foreach($v as $subK => $subV) {
                                        if(!is_array($subV) && !in_array($subK, $hiddenKeys) && $subV !== null && trim($subV) !== '') {
                                            $newKey = $k . ' ' . $subK;
                                            $filasUnificadas[$newKey] = $subV;
                                        }
                                    }
                                }
                            } else {
                                if($v !== null && trim($v) !== '') {
                                    $filasUnificadas[$k] = $v;
                                }
                            }
                        }
                    @endphp

                    @if(count($filasUnificadas) > 0)
                        <table>
                            @foreach($filasUnificadas as $label => $val)
                            <tr>
                                <td class="bg-label">{{ strtoupper(str_replace(['_', 'inst'], [' ', 'entidad'], $label)) }}:</td>
                                <td class="uppercase">{{ is_bool($val) ? ($val ? 'SI' : 'NO') : $val }}</td>
                            </tr>
                            @endforeach
                        </table>
                    @endif

                    {{-- Fotos (Compatibilidad para ambos formatos de guardado) --}}
                    @php $fotosEncontradas = false; @endphp
                    
                    {{-- 1. Formato nuevo (array 'fotos_evidencia') --}}
                    @if(!empty($cont['fotos_evidencia']) && is_array($cont['fotos_evidencia']))
                        @foreach($cont['fotos_evidencia'] as $ruta)
                            @php
                                $isFullUrl = !empty($ruta) && str_starts_with($ruta, 'http');
                                $realPath = !empty($ruta) ? ($isFullUrl ? $ruta : public_path('storage/' . $ruta)) : null;
                            @endphp
                            @if($realPath && ($isFullUrl || file_exists($realPath)))
                                @if(!$fotosEncontradas) 
                                    <div style="margin-top:8px;"><div style="font-weight:700; font-size:9px; margin-bottom:6px;">EVIDENCIAS FOTOGRÁFICAS</div><div class="foto-grid"> 
                                    @php $fotosEncontradas = true; @endphp
                                @endif
                                <div class="foto-wrapper"><img src="{{ $realPath }}"></div>
                            @endif
                        @endforeach
                        @if($fotosEncontradas) </div></div> @endif
                    @endif
                    
                    {{-- 2. Formato antiguo (claves 'foto_1', 'foto_2') --}}
                    @if(!$fotosEncontradas && (!empty($cont['foto_1']) || !empty($cont['foto_2'])))
                         <div style="margin-top:8px;">
                            <div style="font-weight:700; font-size:9px; margin-bottom:6px;">EVIDENCIAS FOTOGRÁFICAS</div>
                            <div class="foto-grid">
                                @foreach(['foto_1', 'foto_2'] as $fKey)
                                    @php
                                        $fPath = $cont[$fKey] ?? null;
                                        $fIsFull = $fPath && str_starts_with($fPath, 'http');
                                        $fRealPath = $fPath ? ($fIsFull ? $fPath : public_path('storage/' . $fPath)) : null;
                                    @endphp
                                    @if($fRealPath && ($fIsFull || file_exists($fRealPath)))
                                        <div class="foto-wrapper"><img src="{{ $fRealPath }}"></div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                @else
                    <div style="padding: 10px; border: 1px solid #e2e8f0; font-style: italic;">Sin información detallada registrada.</div>
                @endif
            </div>
            @php $contadorModulo++; @endphp
        @endif
    @endforeach

    {{-- HE ELIMINADO EL BLOQUE "RED DE SEGURIDAD (EXTRA)" PARA EVITAR QUE APAREZCA 'CONFIG MODULOS' O BASURA --}}

    <div class="section-header">3. DETALLE DE EQUIPAMIENTO POR MÓDULO</div>
    
    @if($equipos && $equipos->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="5%">N°</th>
                    <th width="15%">MODULO</th>
                    <th width="15%">SERIE/CÓDIGO</th>
                    <th width="8%">CANT.</th>
                    <th width="20%">DESCRIPCIÓN DEL EQUIPO</th>
                    <th width="10%">ESTADO</th>
                    <th width="12%">PROPIEDAD</th>
                    <th width="15%">OBSERVACIÓN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($equipos as $index => $eq)
                    <tr class="uppercase" style="font-size: 8px;">
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
        <p style="padding-left: 10px; font-style: italic; color: #64748b;">No se registró equipamiento tecnológico en este monitoreo.</p>
    @endif

    <div class="no-break">
        <div class="section-header">4. PANEL FOTOGRÁFICO DE EVIDENCIAS</div>
        @if($acta->foto1 || $acta->foto2)
            <div class="foto-grid">
                @if($acta->foto1 && file_exists(public_path('storage/' . $acta->foto1)))
                    <div class="foto-wrapper">
                        <img src="{{ public_path('storage/' . $acta->foto1) }}">
                        <div class="foto-caption">EVIDENCIA 01 - REGISTRO DE MONITOREO</div>
                    </div>
                @endif

                @if($acta->foto2 && file_exists(public_path('storage/' . $acta->foto2)))
                    <div class="foto-wrapper">
                        <img src="{{ public_path('storage/' . $acta->foto2) }}">
                        <div class="foto-caption">EVIDENCIA 02 - REGISTRO DE MONITOREO</div>
                    </div>
                @endif
            </div>
        @else
            <p style="padding-left: 10px; font-style: italic; color: #64748b;">No se adjuntaron fotografías de evidencia en esta acta.</p>
        @endif
    </div>

    <div class="no-break">
        <div class="section-header">5. Firmas de Conformidad</div>
        <div class="firmas-grid">
            <div class="firma-card">
                <div class="linea-firma"></div>
                <span class="nombre-firma">{{ $monitor['nombre'] ?? 'MONITOR' }}</span>
                <span class="cargo-firma">Implementador</span>
            </div>
            <div class="firma-card">
                <div class="linea-firma"></div>
                <span class="nombre-firma">{{ $jefe['nombre'] ?? 'JEFE DE ESTABLECIMIENTO' }}</span>
                <span class="cargo-firma">Jefe del Establecimiento</span>
            </div>
            @foreach($equipoMonitoreo as $miembro)
                <div class="firma-card">
                    <div class="linea-firma"></div>
                    <span class="nombre-firma">{{ strtoupper(trim(($miembro->apellido_paterno ?? '') . ' ' . ($miembro->apellido_materno ?? ''). ' ' . ($miembro->nombres ?? ''))) }}</span>
                    <span class="cargo-firma">{{ strtoupper($miembro->institucion ?? 'Acompañante Técnico') }}</span>
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>