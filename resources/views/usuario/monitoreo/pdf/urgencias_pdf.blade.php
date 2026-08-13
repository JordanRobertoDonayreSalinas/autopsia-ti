<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Módulo 18: Urgencias y Emergencias - Acta {{ $acta->numero_acta }}</title>
    <style>
        /* MÁRGENES: 2.5cm abajo para reservar espacio al pie de página del Controlador */
        @page { margin: 1.2cm 1.5cm 2.5cm 1.5cm; }
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; }
        
        /* ENCABEZADO */
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #dc2626; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 16px; text-transform: uppercase; color: #dc2626; font-weight: bold; }
        .acta-info { font-size: 10px; color: #64748b; font-weight: bold; text-transform: uppercase; margin-top: 5px; }

        /* TÍTULOS DE SECCIÓN (Estilo Rojo) */
        .section-title { 
            background-color: #fef2f2; 
            padding: 6px 10px; 
            font-weight: bold; 
            text-transform: uppercase; 
            border-left: 4px solid #dc2626; 
            margin-top: 15px; 
            margin-bottom: 5px; 
            font-size: 10px; 
            color: #991b1b;
        }

        /* TABLAS */
        table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 5px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; vertical-align: middle; word-wrap: break-word; }
        th { background-color: #f8fafc; color: #475569; font-size: 8.5px; text-transform: uppercase; }
        
        /* COLUMNA ETIQUETA */
        .bg-label { background-color: #f8fafc; font-weight: bold; width: 35%; text-transform: uppercase; color: #334155; font-size: 9px; }
        .uppercase { text-transform: uppercase; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

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

        /* ESTANDARIZACIÓN DE FIRMAS */
        .firma-section { margin-top: 30px; width: 100%; }
        .firma-container { width: 380px; margin: 0 auto; }
        .firma-box { 
            border: 1px solid #cbd5e1; 
            border-radius: 15px; 
            padding: 20px; 
            padding-top: 100px;
            text-align: center;
            background-color: #f8fafc;
        }
        .firma-linea { border-top: 1px solid #000; margin: 10px auto 5px auto; width: 85%; }
        .firma-nombre { font-weight: bold; font-size: 11px; text-transform: uppercase; color: #1e293b; }
        .firma-label { font-size: 9px; color: #475569; margin-top: 2px; text-transform: uppercase; }
    </style>
</head>
<body>

    @php $n = 1; @endphp

    {{-- ENCABEZADO --}}
    <div class="header">
        <h1>Módulo 18: Urgencias y Emergencias</h1>
        <div class="acta-info">
            ACTA N° {{ str_pad($acta->numero_acta, 3, '0', STR_PAD_LEFT) }} | 
            ESTABLECIMIENTO: {{ $acta->establecimiento->codigo }} - {{ strtoupper($acta->establecimiento->nombre) }} | 
            FECHA: {{ isset($detalle->contenido['fecha']) ? date('d/m/Y', strtotime($detalle->contenido['fecha'])) : date('d/m/Y') }}
        </div>
    </div>

    {{-- 1. DATOS GENERALES --}}
    <div class="section-title">{{ $n++ }}. DATOS GENERALES</div>
    <table>
        <tbody>
            <tr>
                <td class="bg-label">Turno</td>
                <td class="uppercase">{{ $detalle->contenido['turno'] ?? '---' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- 2. DATOS DEL PROFESIONAL --}}
    <div class="section-title">{{ $n++ }}. DATOS DEL PROFESIONAL</div>
    <table>
        <tbody>
            <tr>
                <td class="bg-label">Apellidos y Nombres</td>
                <td class="uppercase">
                    {{ $detalle->contenido['rrhh']['apellido_paterno'] ?? '' }} 
                    {{ $detalle->contenido['rrhh']['apellido_materno'] ?? '' }} 
                    {{ $detalle->contenido['rrhh']['nombres'] ?? '' }}
                </td>
            </tr>
            <tr>
                <td class="bg-label">{{ $detalle->contenido['rrhh']['tipo_doc'] ?? 'DOCUMENTO DE IDENTIDAD' }}</td>
                <td>{{ $detalle->contenido['rrhh']['doc'] ?? '---' }}</td>
            </tr>
            <tr>
                <td class="bg-label">Cargo / Profesión</td>
                <td class="uppercase">{{ $detalle->contenido['rrhh']['cargo'] ?? '---' }}</td>
            </tr>
            <tr>
                <td class="bg-label">¿Utiliza SIHCE?</td>
                <td class="uppercase">{{ $detalle->contenido['cuenta_sihce'] ?? '---' }}</td>
            </tr>
            @if(($detalle->contenido['cuenta_sihce'] ?? '') == 'SI')
            <tr>
                <td class="bg-label">¿Firmó Declaración Jurada?</td>
                <td class="uppercase">{{ $detalle->contenido['firmo_dj'] ?? '---' }}</td>
            </tr>
            <tr>
                <td class="bg-label">¿Firmó Confidencialidad?</td>
                <td class="uppercase">{{ $detalle->contenido['firmo_confidencialidad'] ?? '---' }}</td>
            </tr>
            @else
            <tr>
                <td class="bg-label">Documentación SIHCE</td>
                <td style="color: #64748b;">NO APLICA (NO UTILIZA SIHCE)</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- 3. TIPO DE DNI Y FIRMA DIGITAL --}}
    {{-- LÓGICA: Solo mostrar si el tipo de documento es DNI --}}
    @if(($detalle->contenido['rrhh']['tipo_doc'] ?? 'DNI') === 'DNI')
    <div class="section-title">{{ $n++ }}. DETALLE DE DNI Y FIRMA DIGITAL</div>
    <table>
        <tbody>
            <tr>
                <td class="bg-label">Tipo de DNI</td>
                <td class="uppercase">{{ $detalle->contenido['tipo_dni'] ?? '---' }}</td>
            </tr>
            @if(($detalle->contenido['tipo_dni'] ?? '') == 'ELECTRONICO')
            <tr>
                <td class="bg-label">Versión DNIe</td>
                <td class="uppercase">{{ $detalle->contenido['version_dnie'] ?? '---' }}</td>
            </tr>
            <tr>
                <td class="bg-label">¿Firma Digitalmente en SIHCE?</td>
                <td class="uppercase">{{ $detalle->contenido['firma_digital_sihce'] ?? '---' }}</td>
            </tr>
            @endif
            <tr>
                <td class="bg-label">Observaciones</td>
                <td class="uppercase">{{ $detalle->contenido['observaciones_dni'] ?? 'Sin observaciones' }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    {{-- 4. ACCESO Y CAPACITACIÓN --}}
    <div class="section-title">{{ $n++ }}. DETALLES DE CAPACITACIÓN</div>
    <table>
        <tbody>
            <tr>
                <td class="bg-label">¿Recibió Capacitación?</td>
                <td class="uppercase">{{ $detalle->contenido['recibio_capacitacion'] ?? '---' }}</td>
            </tr>
            @if(($detalle->contenido['recibio_capacitacion'] ?? '') == 'SI')
            <tr>
                <td class="bg-label">Entidad Capacitadora</td>
                <td class="uppercase">{{ $detalle->contenido['inst_que_lo_capacito'] ?? '---' }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- 5. EQUIPOS DE COMPUTO --}}
    <div class="section-title">{{ $n++ }}. EQUIPOS DE COMPUTO</div>
    @if($equipos->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="25%">DESCRIPCIÓN</th>
                    <th width="10%" class="text-center">CANT.</th>
                    <th width="15%" class="text-center">ESTADO</th>
                    <th width="15%" class="text-center">PROPIEDAD</th>
                    <th width="15%" class="text-center">N. SERIE</th>
                    <th width="20%">OBSERVACIÓN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($equipos as $eq)
                <tr>
                    <td class="uppercase">{{ $eq->descripcion }}</td>
                    <td class="text-center font-bold">{{ $eq->cantidad }}</td>
                    <td class="text-center uppercase">{{ $eq->estado }}</td>
                    <td class="text-center uppercase">{{ $eq->propio }}</td>
                    <td class="text-center uppercase">{{ $eq->nro_serie ?? '---' }}</td>
                    <td class="uppercase">{{ $eq->observacion ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="color: #94a3b8; font-style: italic; padding: 10px; border: 1px solid #e2e8f0;">SIN EQUIPAMIENTO REGISTRADO</div>
    @endif

    {{-- SECCIÓN: CONECTIVIDAD --}}
    @php
        $tipoConectividad = $detalle->contenido['tipo_conectividad'] ?? null;
        $wifiFuente       = $detalle->contenido['wifi_fuente'] ?? null;
        $operadorServicio = $detalle->contenido['operador_servicio'] ?? null;
    @endphp
    <div class="section-title">{{ $n++ }}. CONECTIVIDAD</div>
    <table>
        <tbody>
            <tr>
                <td class="bg-label">Tipo de Conectividad</td>
                <td class="uppercase">{{ $tipoConectividad ?? '---' }}</td>
            </tr>
            @if($tipoConectividad == 'WIFI')
            <tr>
                <td class="bg-label">Fuente de WiFi</td>
                <td class="uppercase">{{ $wifiFuente ?? '---' }}</td>
            </tr>
            @endif
            @if($tipoConectividad != 'SIN CONECTIVIDAD')
            <tr>
                <td class="bg-label">Operador de Servicio</td>
                <td class="uppercase">{{ $operadorServicio ?? '---' }}</td>
            </tr>
            @endif
        @php
            $v_desc = $detalle->contenido['velocidad_descarga'] ?? $detalle->contenido['velocidad_internet_cantidad'] ?? null;
            $v_sub  = $detalle->contenido['velocidad_subida'] ?? null;
            $v_desc_uni = $detalle->contenido['velocidad_descarga_unidad'] ?? $detalle->contenido['velocidad_internet_unidad'] ?? 'Mbps';
            $v_sub_uni = $detalle->contenido['velocidad_subida_unidad'] ?? $detalle->contenido['velocidad_internet_unidad'] ?? 'Mbps';
        @endphp
        @if($tipoConectividad != 'SIN CONECTIVIDAD' && ($v_desc || $v_sub))
        <tr>
            
            <td class="bg-label">Velocidad Descarga</td>
            <td class="uppercase">{{ $v_desc ? $v_desc . ' ' . $v_desc_uni : '---' }}</td>
        </tr>
        <tr>
            
            <td class="bg-label">Velocidad Subida</td>
            <td class="uppercase">{{ $v_sub ? $v_sub . ' ' . $v_sub_uni : '---' }}</td>
        </tr>
        
        @endif
        </tbody>
    </table>

    {{-- 6. SOPORTE --}}
    <div class="section-title">{{ $n++ }}. SOPORTE</div>
    <table>
        <tbody>
            <tr>
                <td class="bg-label">Ante dificultades comunica a</td>
                <td class="uppercase">{{ $detalle->contenido['inst_a_quien_comunica'] ?? '---' }}</td>
            </tr>
            <tr>
                <td class="bg-label">Medio utilizado</td>
                <td class="uppercase">{{ $detalle->contenido['medio_que_utiliza'] ?? '---' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- 7. PROCESOS Y CALIDAD --}}
    <div class="section-title">{{ $n++ }}. PROCESOS Y CALIDAD</div>
    <table>
        <tbody>
            {{-- Campos específicos de Urgencias --}}
            <tr>
                <td class="bg-label">Prioridad de Triaje Predominante</td>
                <td class="uppercase">{{ $detalle->contenido['triaje_prioridad'] ?? '---' }}</td>
            </tr>
            <tr>
                <td class="bg-label">Tiempo Promedio de Espera</td>
                <td class="uppercase">{{ $detalle->contenido['tiempo_espera'] ?? '---' }}</td>
            </tr>
            {{-- Campos comunes --}}
            
        </tbody>
    </table>

    {{-- 8. COMENTARIOS --}}
    <div class="section-title">{{ $n++ }}. COMENTARIOS</div>
    <div style="border: 1px solid #e2e8f0; padding: 10px; min-height: 40px; text-transform: uppercase; font-size: 10px;">
        {{ $detalle->contenido['comentarios'] ?? 'SIN COMENTARIOS REGISTRADOS.' }}
    </div>

    {{-- 9. EVIDENCIA FOTOGRÁFICA --}}
    <div class="section-title">{{ $n++ }}. EVIDENCIA FOTOGRÁFICA</div>

    @php
        $fotos = [];
        $evidencia = $detalle->contenido['foto_evidencia'] ?? null;
        if (!empty($evidencia)) {
            $arrayEvidencia = is_array($evidencia) ? $evidencia : [$evidencia];
            foreach ($arrayEvidencia as $fotoPath) {
                if (!empty($fotoPath)) {
                    $path = str_starts_with($fotoPath, 'http') ? $fotoPath : storage_path('app/public/' . $fotoPath);
                    if (str_starts_with($fotoPath, 'http')) {
                        $fotos[] = $fotoPath;
                    } elseif (file_exists($path)) {
                        $fotos[] = $path;
                    } elseif (file_exists(public_path('storage/' . $fotoPath))) {
                        $fotos[] = public_path('storage/' . $fotoPath);
                    }
                }
            }
        }
        $cantidad = count($fotos);
    @endphp

    @if ($cantidad > 0)
        @if ($cantidad == 1)
        <div style="width: 100%; text-align: center; margin-top: 15px;">
            <div style="display: inline-block; border: 1px solid #e2e8f0; padding: 5px; background: #fff; border-radius: 10px;">
                <img src="{{ $fotos[0] }}" style="width: 100%; height: 250px; object-fit: cover; border-radius: 8px;">
            </div>
        </div>
        @else
        <table style="width: 100%; border: none; margin-top: 10px;">
            <tr>
                @foreach ($fotos as $index => $fotoUrl)
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

                @if ($cantidad % 2 != 0)
                    <td style="border: none;"></td>
                @endif
            </tr>
        </table>
        @endif
    @else
        <div class="no-evidence-box">NO SE ADJUNTÓ EVIDENCIA FOTOGRÁFICA.</div>
    @endif

    {{-- 10. FIRMA DE CONFORMIDAD --}}
    <div class="firma-section">
        <div class="section-title">{{ $n++ }}. FIRMA</div>
        <div class="firma-container">
            <div class="firma-box">
                @include('usuario.firmas.pdf_stamp', ['firma' => $firmaEntrevistado])
                <div class="firma-linea"></div>
                <div class="firma-nombre">
                    
                    {{ $detalle->contenido['rrhh']['apellido_paterno'] ?? '' }} 
                    {{ $detalle->contenido['rrhh']['apellido_materno'] ?? '' }}
                    {{ $detalle->contenido['rrhh']['nombres'] ?? '' }} 
                </div>
                
                {{-- TEXTO FIJO SOLICITADO --}}
                <div class="firma-label">
                    FIRMA DEL PROFESIONAL ENTREVISTADO
                </div>
                
                <div class="firma-label">
                    {{ $detalle->contenido['rrhh']['tipo_doc'] ?? 'DNI' }}: {{ $detalle->contenido['rrhh']['doc'] ?? '________' }}
                </div>
            </div>
        </div>
    </div>

</body>
</html>