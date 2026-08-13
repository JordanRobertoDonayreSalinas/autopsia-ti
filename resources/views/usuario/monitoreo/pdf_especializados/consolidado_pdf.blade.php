<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Consolidado CSMC</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        
        /* HEADER */
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #0f766e; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 16px; color: #115e59; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 10px; color: #555; }

        /* SECCIÓN DE CONTROL */
        .control-section { margin-bottom: 30px; }
        .control-title {
            font-size: 11px;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            border-left: 5px solid #0f172a;
            padding-left: 10px;
            margin-bottom: 10px;
        }
        .control-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .control-table th { background-color: #f1f5f9; color: #334155; font-weight: bold; text-align: left; padding: 6px; border: 1px solid #e2e8f0; width: 30%; }
        .control-table td { border: 1px solid #e2e8f0; padding: 6px; color: #0f172a; }

        .team-header { font-size: 10px; font-weight: bold; color: #334155; margin-bottom: 5px; text-transform: uppercase; }
        
        /* MÓDULOS */
        .module-container { margin-bottom: 20px; page-break-inside: avoid; border: 1px solid #ddd; padding: 10px; border-radius: 5px; }
        .module-header { 
            background-color: #0f766e; color: white; padding: 6px 10px; 
            font-size: 12px; font-weight: bold; text-transform: uppercase; 
            border-radius: 3px; margin-bottom: 10px;
        }
        .section-title {
            font-size: 10px; font-weight: bold; color: #0f766e;
            border-bottom: 1px solid #ccfbf1; margin-top: 8px; margin-bottom: 4px; text-transform: uppercase;
        }

        /* TABLAS DATOS */
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .data-table th, .data-table td { border: 1px solid #e2e8f0; padding: 4px 6px; text-align: left; vertical-align: top; }
        .data-table th { background-color: #f0fdfa; color: #0f766e; font-weight: bold; font-size: 9px; width: 60%; } /* Ajusté el ancho para que la pregunta entre bien */
        
        /* TABLA EQUIPOS */
        .table-equipos { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .table-equipos th, .table-equipos td { border: 1px solid #e2e8f0; padding: 6px; }
        .table-equipos th { background-color: #1e293b; color: #fff; text-align: center; font-size: 9px; text-transform: uppercase; }
        .table-equipos td { text-align: center; font-size: 9px; vertical-align: middle; }
        .text-left { text-align: left !important; }
        .text-center { text-align: center !important; }
        .badge { background-color: #e2e8f0; padding: 2px 4px; border-radius: 3px; font-weight: bold; font-size: 8px; }

        /* FOTOS */
        .photo-section { margin-top: 20px; page-break-inside: avoid; }
        .photo-grid { width: 100%; text-align: center; margin-top: 10px; }
        .photo-container {
            display: inline-block; width: 45%; margin: 0 5px 10px 5px;
            border: 4px solid #fff; box-shadow: 0 0 5px rgba(0,0,0,0.1);
            border-radius: 8px; overflow: hidden; vertical-align: top;
        }
        .photo-img { width: 100%; height: 250px; object-fit: cover; border-radius: 10px; }

        /* FIRMAS */
        .signatures-section { margin-top: 30px; page-break-inside: avoid; }
        .signatures-table { width: 100%; border: none; margin-top: 10px; }
        .signatures-table td { border: none; padding: 8px; vertical-align: top; }
        
        .signature-box {
            border: 1px solid #94a3b8;
            border-radius: 8px;
            padding: 70px 10px 10px 10px;
            text-align: center;
            height: 60px; 
            background-color: #fff;
        }
        .signature-line {
            border-bottom: 1px solid #64748b;
            width: 85%;
            margin: 0 auto 5px auto;
        }
        .signature-name { 
            font-weight: bold; font-size: 9px; color: #0f172a; text-transform: uppercase; margin-bottom: 2px;
        }
        .signature-role { 
            font-size: 8px; color: #64748b; font-weight: bold; text-transform: uppercase; 
        }

    </style>
</head>
<body>

    <div class="header">
        <h1>Reporte Consolidado de Módulos Especializados</h1>
        <p><strong>Establecimiento:</strong> {{ $acta->establecimiento->nombre ?? 'NO DEFINIDO' }}</p>
        <p><strong>Fecha de Generación:</strong> {{ date('d/m/Y H:i') }} | <strong>Acta N°:</strong> {{ str_pad($acta->numero_acta, 3, '0', STR_PAD_LEFT) }}</p>
    </div>

    {{-- INFORMACIÓN DE CONTROL --}}
    <div class="control-section">
        <div class="control-title">INFORMACIÓN DE CONTROL</div>
        
        <table class="control-table">
            <tr>
                <th>Fecha de Monitoreo:</th>
                <td>{{ $acta->created_at ? $acta->created_at->format('d/m/Y') : date('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Monitor / Implementador:</th>
                <td>{{ $monitor['nombre'] }}</td>
            </tr>
            <tr>
                <th>Jefe del Establecimiento:</th>
                <td>{{ $jefe['nombre'] }}</td>
            </tr>
        </table>

        @if(isset($equipoMonitoreo) && count($equipoMonitoreo) > 0)
            <div class="team-header">EQUIPO DE ACOMPAÑAMIENTO:</div>
            <table class="control-table" style="font-size: 9px;">
                <thead>
                    <tr>
                        <th style="background-color: #f8fafc; color: #64748b; width: 50%;">NOMBRE COMPLETO</th>
                        <th style="background-color: #f8fafc; color: #64748b; width: 25%;">DNI</th>
                        <th style="background-color: #f8fafc; color: #64748b; width: 25%;">CARGO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($equipoMonitoreo as $miembro)
                    <tr>
                        <td style="text-transform: uppercase;">{{ $miembro->apellido_paterno }} {{ $miembro->apellido_materno }} {{ $miembro->nombres ?? '-' }}</td>
                        <td>{{ $miembro->doc ?? '-' }}</td>
                        <td style="text-transform: uppercase;">{{ $miembro->cargo ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- ITERACIÓN DE MÓDULOS --}}
    {{-- @php $modulosOrdenados = $modulos->sortBy('modulo_nombre'); @endphp --}}

    @forelse($modulos as $modulo)
        @php
            $contenido = is_string($modulo->contenido) ? json_decode((string)$modulo->contenido, true) : $modulo->contenido;
            $consultorio = $contenido['detalle_del_consultorio'] ?? [];
            $profesional = $contenido['datos_del_profesional'] ?? [];
            $admin       = $contenido['documentacion_administrativa'] ?? [];
            $dni         = $contenido['detalle_de_dni_y_firma_digital'] ?? [];
            $capacitacion= $contenido['detalles_de_capacitacion'] ?? [];
            $soporte     = $contenido['soporte'] ?? [];
            $evidencias  = $contenido['comentarios_y_evidencias'] ?? [];
        @endphp

        <div class="module-container">
            <div class="module-header">{{ $modulo->modulo_nombre }}</div>

            @if(str_contains(strtolower($modulo->modulo_nombre), 'infraestructura') || str_contains(strtolower($modulo->modulo_nombre), 'croquis'))
                {{-- Renderizado especial para Infraestructura 3D --}}
                @php
                    $elementos = $contenido['elementos'] ?? [];
                    $conteo = ['ambiente' => 0, 'hardware' => 0, 'puerta' => 0, 'calle' => 0, 'sistema' => 0];
                    foreach($elementos as $el) {
                        $t = strtolower($el['type'] ?? 'otro');
                        if(isset($conteo[$t])) $conteo[$t]++;
                    }
                @endphp
                <div class="section-title">Resumen del Diseño 2D</div>
                <table class="data-table">
                    <tr>
                        <th>Ambientes Registrados</th>
                        <td>{{ $conteo['ambiente'] }} unidades</td>
                    </tr>
                    <tr>
                        <th>Equipamiento TI / Hardware</th>
                        <td>{{ $conteo['hardware'] }} elementos</td>
                    </tr>
                    <tr>
                        <th>Accesos / Puertas</th>
                        <td>{{ $conteo['puerta'] }} unidades</td>
                    </tr>
                    <tr>
                        <th>Sistemas / Software</th>
                        <td>{{ $conteo['sistema'] }} aplicaciones</td>
                    </tr>
                    <tr>
                        <th>Conexiones de Red</th>
                        <td>{{ count($contenido['conexiones'] ?? []) }} enlaces establecidos</td>
                    </tr>
                </table>
                @if(!empty($contenido['imagen_path']))
                    <div style="text-align: center; margin-top: 5px;">
                        @php
                            $path_rel = $contenido['imagen_path'] ?? '';
                            $possible_paths = [
                                storage_path('app/public/' . $path_rel),
                                public_path('storage/' . $path_rel),
                                base_path('storage/app/public/' . $path_rel),
                            ];
                            
                            $img_final = null;
                            foreach($possible_paths as $p) {
                                if(!empty($path_rel) && file_exists($p)) {
                                    $img_final = $p;
                                    break;
                                }
                            }

                            $base64 = null;
                            if($img_final) {
                                $type = pathinfo($img_final, PATHINFO_EXTENSION);
                                $data = file_get_contents($img_final);
                                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                            }
                        @endphp
                        @if($base64)
                            <img src="{{ $base64 }}" style="width: 100%; border: 1px solid #ccfbf1; border-radius: 5px;">
                        @endif
                    </div>
                @endif
                <p style="font-size: 8px; font-style: italic; color: #666; margin-top: 5px;">* Consulte el reporte individual del módulo para el detalle gráfico y tablas de dimensiones.</p>
            @else
                {{-- Bloque 1: Consultorio y Profesional (Original) --}}
                <table class="data-table" style="margin-bottom: 0;">
                    <tr>
                        <td width="50%" style="border:none; padding: 0 5px 0 0;">
                            <div class="section-title">Detalle del Consultorio</div>
                            <table class="data-table">
                                <tr><th>Fecha Monitoreo</th><td>{{ $consultorio['fecha_monitoreo'] ?? '-' }}</td></tr>
                                <tr><th>Turno</th><td>{{ $consultorio['turno'] ?? '-' }}</td></tr>
                                <tr><th>N° Consultorios</th><td>{{ $consultorio['num_consultorios'] ?? '-' }}</td></tr>
                                <tr><th>Denominación</th><td>{{ $consultorio['denominacion'] ?? '-' }}</td></tr>
                            </table>
                        </td>
                        <td width="50%" style="border:none; padding: 0 0 0 5px;">
                            <div class="section-title">Datos del Profesional</div>
                            <table class="data-table">
                                <tr><th>Profesional</th><td>{{ $profesional['apellido_paterno'] ?? '' }} {{ $profesional['apellido_materno'] ?? '' }}, {{ $profesional['nombres'] ?? '' }}</td></tr>
                                <tr><th>Documento</th><td><span class="badge">{{ $profesional['tipo_doc'] ?? 'DNI' }}</span> {{ $profesional['doc'] ?? '-' }}</td></tr>
                                <tr><th>Cargo</th><td>{{ $profesional['cargo'] ?? '-' }}</td></tr>
                                <tr><th>Contacto</th><td>{{ $profesional['email'] ?? '-' }} <br> {{ $profesional['telefono'] ?? '' }}</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>

                {{-- Bloque 2: Admin y DNI --}}
                <div class="section-title">Documentación y Firma Digital</div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 20%;">Utiliza SIHCE</th>
                            <th class="text-center" style="width: 20%;">Firmó DJ</th>
                            <th class="text-center" style="width: 20%;">Firmó Confidencialidad</th>
                            <th class="text-center" style="width: 20%;">Firma Digital Activa</th>
                            <th class="text-center" style="width: 20%;">Tipo DNI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">{{ $admin['utiliza_sihce'] ?? '-' }}</td>
                            <td class="text-center">{{ $admin['firmo_dj'] ?? '-' }}</td>
                            <td class="text-center">{{ $admin['firmo_confidencialidad'] ?? '-' }}</td>
                            <td class="text-center">{{ $dni['firma_digital_sihce'] ?? '-' }}</td>
                            <td class="text-center">{{ $dni['tipo_dni'] ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
                @if(!empty($dni['observaciones_dni']))
                <div style="background: #f8fafc; padding: 4px; border: 1px dashed #cbd5e1; font-size: 9px; margin-bottom: 5px;">
                    <strong>Obs. DNI:</strong> {{ $dni['observaciones_dni'] }}
                </div>
                @endif

                {{-- Bloque 3 mejorado: Capacitación y Soporte --}}
                <table style="width: 100%; border-collapse: collapse; margin-top: 5px; border: none;">
                    <tr>
                        <td width="49%" style="vertical-align: top; border: none; padding-right: 5px;">
                            <div class="section-title">DETALLE DE CAPACITACIÓN</div>
                            <table class="data-table">
                                <tr>
                                    <th>¿El personal ha recibido capacitación?</th>
                                    <td class="text-center">{{ $capacitacion['recibio_capacitacion'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Entidad que capacitó</th>
                                    <td class="text-center">{{ $capacitacion['inst_que_lo_capacito'] ?? '-' }}</td>
                                </tr>
                            </table>
                        </td>
                        <td width="2%" style="border: none;"></td>
                        <td width="49%" style="vertical-align: top; border: none; padding-left: 5px;">
                            <div class="section-title">SOPORTE</div>
                            <table class="data-table">
                                <tr>
                                    <th>Ante Dificultades ¿A quién comunica?</th>
                                    <td class="text-center">{{ $soporte['inst_a_quien_comunica'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>¿Qué medio utiliza?</th>
                                    <td class="text-center">{{ $soporte['medio_que_utiliza'] ?? '-' }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                
                @if(!empty($evidencias['comentarios']))
                    <div class="section-title">Observaciones / Comentarios</div>
                    <div style="background: #fff; padding: 5px; border: 1px solid #ddd; font-style: italic;">
                        {{ $evidencias['comentarios'] }}
                    </div>
                @endif
            @endif
        </div>
    @empty
        <div style="padding: 20px; text-align: center; border: 1px dashed #999; color: #777;">
            No se han registrado módulos especializados para esta acta.
        </div>
    @endforelse

    {{-- DETALLE DE EQUIPAMIENTO --}}
    @if(count($equipos) > 0)
        <div class="module-header" style="margin-top: 25px;">DETALLE DE EQUIPAMIENTO POR MÓDULO</div>
        <table class="table-equipos">
            <thead>
                <tr>
                    <th width="5%">N°</th>
                    <th width="25%">MÓDULO</th>
                    <th width="15%">SERIE/CÓDIGO</th>
                    <th width="5%">CANT.</th>
                    <th width="25%">DESCRIPCIÓN DEL EQUIPO</th>
                    <th width="12%">ESTADO</th>
                    <th width="13%">PROPIEDAD</th>
                </tr>
            </thead>
            <tbody>
                @foreach($equipos as $eq)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="text-left" style="font-size: 8px;">{{ $eq->modulo }}</td>
                    <td style="font-family: monospace;">{{ $eq->nro_serie }}</td>
                    <td>{{ $eq->cantidad }}</td>
                    <td class="text-left">{{ $eq->descripcion }}</td>
                    <td>{{ $eq->estado }}</td>
                    <td>{{ $eq->propio }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- PANEL FOTOGRÁFICO --}}
    @if($acta->foto1 || $acta->foto2)
        <div class="module-header" style="margin-top: 25px;">PANEL FOTOGRÁFICO DE EVIDENCIAS</div>
        <div class="photo-section">
            <div class="photo-grid">
                @if($acta->foto1)
                    @php 
                        $isFullUrl1 = str_starts_with($acta->foto1, 'http');
                        $path1 = $isFullUrl1 ? $acta->foto1 : storage_path('app/public/' . $acta->foto1);
                        if(!$isFullUrl1 && !file_exists($path1)) $path1 = public_path('storage/' . $acta->foto1);
                    @endphp
                    @if($isFullUrl1 || file_exists($path1))
                        <div class="photo-container"><img src="{{ $path1 }}" class="photo-img"></div>
                    @endif
                @endif

                @if($acta->foto2)
                    @php 
                        $isFullUrl2 = str_starts_with($acta->foto2, 'http');
                        $path2 = $isFullUrl2 ? $acta->foto2 : storage_path('app/public/' . $acta->foto2);
                        if(!$isFullUrl2 && !file_exists($path2)) $path2 = public_path('storage/' . $acta->foto2);
                    @endphp
                    @if($isFullUrl2 || file_exists($path2))
                        <div class="photo-container"><img src="{{ $path2 }}" class="photo-img"></div>
                    @endif
                @endif
            </div>
        </div>
    @endif

    {{-- FIRMAS DE CONFORMIDAD --}}
    <div class="module-header" style="margin-top: 25px;">FIRMAS DE CONFORMIDAD</div>
    
    <div class="signatures-section">
        <table class="signatures-table">
            <tr>
                <td width="48%">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div class="signature-name">{{ $monitor['nombre'] }}</div>
                        <div class="signature-role">IMPLEMENTADOR</div>
                    </div>
                </td>
                <td width="4%"></td>
                <td width="48%">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div class="signature-name">{{ $jefe['nombre'] }}</div>
                        <div class="signature-role">{{ $jefe['cargo'] }}</div>
                    </div>
                </td>
            </tr>
        </table>

        @if(isset($equipoMonitoreo) && count($equipoMonitoreo) > 0)
            @if($equipoMonitoreo->count() == 1)
                @php $miembro = $equipoMonitoreo->first(); @endphp
                <table class="signatures-table" style="margin-top: 15px;">
                    <tr>
                        <td width="25%"></td>
                        <td width="50%">
                            <div class="signature-box">
                                <div class="signature-line"></div>
                                <div class="signature-name">
                                    {{ mb_strtoupper($miembro->apellido_paterno . ' ' . $miembro->apellido_materno . ' ' . $miembro->nombres) }}
                                </div> 
                                <div class="signature-role">{{ mb_strtoupper($miembro->cargo ?? 'ACOMPAÑANTE') }}</div>
                            </div>
                        </td>
                        <td width="25%"></td>
                    </tr>
                </table>
            @else
                <table class="signatures-table" style="margin-top: 15px;">
                    @foreach($equipoMonitoreo->chunk(2) as $row)
                        <tr>
                            @foreach($row as $miembro)
                                <td width="48%">
                                    <div class="signature-box">
                                        <div class="signature-line"></div>
                                        <div class="signature-name">
                                            {{ mb_strtoupper($miembro->apellido_paterno . ' ' . $miembro->apellido_materno . ' ' . $miembro->nombres) }}
                                        </div> 
                                        <div class="signature-role">{{ mb_strtoupper($miembro->cargo ?? 'ACOMPAÑANTE') }}</div>
                                    </div>
                                </td>
                                @if(!$loop->last) <td width="4%"></td> @endif
                            @endforeach
                            @if($row->count() == 1)
                                <td width="4%"></td><td width="48%"></td>
                            @endif
                        </tr>
                        <tr><td colspan="3" style="height: 15px;"></td></tr>
                    @endforeach
                </table>
            @endif
        @endif
    </div>

</body>
</html>