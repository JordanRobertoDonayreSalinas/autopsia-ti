<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Módulo 11: Planificación Familiar - Acta {{ $acta->numero_acta }}</title>
    @include('usuario.monitoreo.pdf.partials.premium_style')
</head>
<body>

    <div class="footer">
        <table class="tab-footer">
            <tr>
                <td class="col-izquierda">SISTEMA DE ACTAS</td>
                <td class="col-derecha">
                    {{-- Se deja vacío para que el script PHP posicione el número aquí --}}
                </td>
            </tr>
        </table>
    </div>

    <div class="header">
        <h1>Módulo 11: Planificación Familiar</h1>
        <div style="font-weight: bold; color: #64748b; font-size: 10px;">
            ACTA N° {{ str_pad($acta->numero_acta, 3, '0', STR_PAD_LEFT) }} | ESTABLECIMIENTO.: {{ strtoupper($acta->establecimiento->codigo) }} - {{ strtoupper($acta->establecimiento->nombre) }} | FECHA: {{ \Carbon\Carbon::parse($acta->fecha ?? ($detalle->fecha_registro ?? now()))->format('d/m/Y') }}
        </div>
    </div>

    {{-- 01. DETALLES DEL CONSULTORIO --}}
    <div class="section-header">Detalles del Consultorio</div>
    <table class="table-data">
        <tr>
            <th width="35%">Cantidad de consultorios:</th>
            <td>{{ $datos['consultorio']['cantidad'] ?? 'N/A' }}</td>
        </tr>
        
        <tr>
            <th>Consultorio entrevistado:</th>
            {{-- Usamos mb_strtoupper para asegurar que se vea en MAYÚSCULAS --}}
            <td>{{ mb_strtoupper($datos['consultorio']['nombre'] ?? 'N/A', 'UTF-8') }}</td>
        </tr>

        <tr>
            <th>Turno:</th>
            <td>{{ mb_strtoupper($datos['consultorio']['turno'] ?? 'N/A', 'UTF-8') }}</td>
        </tr>
    </table>

    <div class="section-header">Datos del Profesional</div>
    <table class="table-data">
        <tr>
            <th width="35%">Apellidos y nombres:</th>
            <td class="uppercase">{{ $detalle->personal_nombre ?? 'N/A' }}</td>
        </tr>
        
        <tr>
            <th>Tipo de documento:</th>
            <td>{{ $datos['personal']['tipo_doc'] ?? 'N/A' }}</td>
        </tr>

        <tr>
            <th>Documento:</th>
            <td>{{ $detalle->personal_dni ?? 'N/A' }}</td>
        </tr>

        <tr>
            <th>Correo:</th>
            <td>{{ $datos['personal']['email'] ?? 'N/A' }}</td>
        </tr>

        <tr>
            <th>Celular:</th>
            <td>{{ $datos['personal']['contacto'] ?? 'N/A' }}</td>
        </tr>
        
        <tr>
            <th>Profesión:</th>
            <td>
                @php
                    $profesion = $datos['personal']['profesion'] ?? 'N/A';
                    $especifique = $datos['personal']['profesion_otro'] ?? '';
                    
                    // Si la profesión es 'OTROS' y hay algo escrito en especifique, mostrar eso.
                    if ($profesion === 'OTROS' && !empty($especifique)) {
                        $profesion = $especifique;
                    }
                @endphp
                {{ mb_strtoupper($profesion, 'UTF-8') }}
            </td>
        </tr>
        
        <tr>
            <th>¿Firmó Declaración Jurada?:</th>
            <td>{{ $datos['documentacion']['firma_dj'] ?? 'NO' }}</td>
        </tr>

        <tr>
            <th>¿Firmó Compromiso de Confidencialidad?:</th>
            <td>{{ $datos['documentacion']['firma_confidencialidad'] ?? 'NO' }}</td>
        </tr>

        <tr>
            <th>¿Utiliza SIHCE?:</th>
            <td>{{ $datos['personal']['utiliza_sihce'] ?? 'NO' }}</td>
        </tr>

    </table>
        
    @if(($datos['personal']['tipo_doc'] ?? '') == 'DNI')
        <div class="section-header">DETALLE DE DNI Y FIRMA DIGITAL</div>
        <table class="table-data" style="text-transform: uppercase;">
            <tr>
                <th>Tipo de DNI:</th>
                <td><strong>{{ $datos['dni_firma']['tipo_dni_fisico'] ?? 'AZUL' }}</strong></td>
            </tr>

            @if(($datos['dni_firma']['tipo_dni_fisico'] ?? '') == 'ELECTRONICO')
            <tr>
                <th>Versión DNIe:</th>
                <td>{{ $datos['dni_firma']['dnie_version'] ?? '---' }}</td>
            </tr>

            <tr>
                <th>¿Firma digitalmente en SIHCE?:</th>
                <td>{{ $datos['dni_firma']['firma_sihce'] ?? 'NO' }}</td>
            </tr>
            @endif
            <tr>
                <th>Observaciones:</th>
                <td> {{ $datos['dni_firma']['observaciones'] ?? 'SIN OBSERVACIONES' }}</td>
            </tr>
        </table>
    @endif
    <div class="section-header">Detalles de Capacitación</div>
    <table class="table-data">
        <tr>
            <th width="30%">¿Recibió Capacitación?</th>
            {{-- Si no recibió, el valor ocupa el resto de la fila --}}
            <td @if(($datos['capacitacion']['recibio'] ?? 'NO') == 'NO') colspan="3" @endif>
                {{ $datos['capacitacion']['recibio'] ?? 'NO' }}
            </td>
            
            {{-- Solo mostramos el ente si la respuesta es SI --}}
            @if(($datos['capacitacion']['recibio'] ?? 'NO') == 'SI')
                <th width="30%">¿De parte de quién?</th>
                <td>
                    @if(isset($datos['capacitacion']['ente']) && is_array($datos['capacitacion']['ente']))
                        {{ implode(', ', $datos['capacitacion']['ente']) }}
                    @elseif(isset($datos['capacitacion']['ente']) && is_string($datos['capacitacion']['ente']))
                        {{ $datos['capacitacion']['ente'] }}
                    @else
                        -
                    @endif
                </td>
            @endif
        </tr>
    </table>

    <div class="section-header">Equipamiento del Consultorio</div>
    <table class="table-data">
        <thead>
            <tr>
                <th class="text-center">Descripción</th>
                <th class="text-center">Cantidad</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Propiedad</th>
                <th class="text-center">N. Serie/C.Pat</th>
                <th class="text-center">Observaciones</th>
                
            </tr>
        </thead>
        <tbody>
            @forelse($equipos as $eq)
            <tr class="uppercase">
                <td class="text-center">{{ $eq->descripcion }}</td>
                <td class="text-center">{{ $eq->cantidad }}</td>
                <td class="text-center">{{ $eq->estado }}</td>
                <td class="text-center">{{ $eq->propio }}</td>
                <td class="text-center">{{ $eq->nro_serie ? ''.$eq->nro_serie : '' }} {{ $eq->observaciones }}</td>
                <td class="text-center">{{ $eq->observacion }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center">No se registraron equipos.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- CONECTIVIDAD --}}
        <div class="section-header">CONECTIVIDAD</div>
        <table class="table-data" style="width: 100%; margin-top: 5px; text-transform: uppercase;">
            @php
                $tipoConectividad = $datos['tipo_conectividad'] ?? null;
                $wifiFuente       = $datos['wifi_fuente'] ?? null;
                $operadorServicio = $datos['operador_servicio'] ?? null;
            @endphp
            <tr>
                <th style="width: 30%; background-color: #f8fafc;">Tipo de conexión:</th>
                <td>{{ $tipoConectividad ?? 'N/A' }}</td>
            </tr>
            @if($tipoConectividad == 'WIFI')
            <tr>
                <th style="background-color: #f8fafc;">Fuente del WiFi:</th>
                <td>{{ $wifiFuente ?? 'N/A' }}</td>
            </tr>
            @endif
            @if($tipoConectividad != 'SIN CONECTIVIDAD')
            <tr>
                <th style="background-color: #f8fafc;">Operador de servicio:</th>
                <td>{{ $operadorServicio ?? 'N/A' }}</td>
            </tr>
            @endif
        @php
            $v_desc = $datos['velocidad_descarga'] ?? $datos['velocidad_internet_cantidad'] ?? null;
            $v_sub  = $datos['velocidad_subida'] ?? null;
            $v_desc_uni = $datos['velocidad_descarga_unidad'] ?? $datos['velocidad_internet_unidad'] ?? 'Mbps';
            $v_sub_uni = $datos['velocidad_subida_unidad'] ?? $datos['velocidad_internet_unidad'] ?? 'Mbps';
        @endphp
        @if($tipoConectividad != 'SIN CONECTIVIDAD' && ($v_desc || $v_sub))
        <tr>
            
            <th style="background-color: #f8fafc;">Velocidad Descarga</th>
            <td>{{ $v_desc ? $v_desc . ' ' . $v_desc_uni : '---' }}</td>
        </tr>
        <tr>
            
            <th style="background-color: #f8fafc;">Velocidad Subida</th>
            <td>{{ $v_sub ? $v_sub . ' ' . $v_sub_uni : '---' }}</td>
        </tr>
        
        @endif
        </table>

        <div class="section-header">Procesos HIS y Tiempos</div>
        <table class="table-data">
            <tr>
                <th width="35%">Tiempo Promedio Atención (min):</th>
                <td class="text-center" width="15%">{{ $detalle->contenido['tiempo_atencion'] ?? '0' }}</td>
                <th width="35%">Atenciones P.F. al mes:</th>
                <td class="text-center" width="15%">{{ $detalle->contenido['atenciones_mes'] ?? '0' }}</td>
            </tr>
        </table>
        <table class="table-data" style="margin-top: 0;">
            <thead>
                <tr>
                    <th width="85%">Indicador de Evaluación</th>
                    <th width="15%" class="text-center">Resultado</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $preguntasHis = [
                        'contingencia'    => '¿Existe cuaderno de contingencia ante caída de internet?',
                        'coord_estad'     => '¿Coordina con estadística para validar cierres mensuales?',
                        'conoce_anul'     => '¿Conoce el procedimiento para anular registros?',
                        'cierre_producc'  => '¿Realiza el cierre de su hoja de producción (HIS)?'
                    ];
                @endphp
                @foreach($preguntasHis as $key => $texto)
                <tr>
                    <td>{{ $texto }}</td>
                    <td class="text-center uppercase">
                        {{ $detalle->contenido['preguntas'][$key] ?? 'N/A' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        @if(($datos['personal']['utiliza_sihce'] ?? '') === 'SI')
        <div class="section-header">Soporte</div>
        <table class="table-data" style="width: 100%; margin-top: 5px; text-transform: uppercase;" >
            <tr>
                <th style="width: 25%; text-align: left; background-color: #f8fafc;">Ante dificultades se comunica con:</th>
                <td style="width: 25%; text-align: left;" class="uppercase">
                    {{ $datos['soporte']['comunica'] ?? '' }}
                </td>

                <th style="width: 20%; text-align: left; background-color: #f8fafc;">Medio que utiliza:</th>
                <td style="width: 30%; text-align: left;" class="uppercase">
                    {{ $datos['soporte']['medio'] ?? '' }}
                </td>
            </tr>
        </table>
        @endif

    <div class="section-header">Comentarios</div>
    
    <div style="
        width: 96%; 
        margin: 10px auto; 
        padding: 15px; 
        border: 1px solid #e2e8f0; 
        background-color: #f8fafc; 
        border-radius: 8px;
        min-height: 60px;
    ">
        <div style="font-size: 10px; color: #1e293b; line-height: 1.6; text-align: justify;">
            @if(!empty($datos['observaciones_generales']))
                {{ $datos['observaciones_generales'] }}
            @else
                <span style="color: #94a3b8; font-style: italic;">No se registraron comentarios.</span>
            @endif
        </div>
    </div>
    <div class="section-header">EVIDENCIA FOTOGRÁFICA</div>

    @php
        $fotos = [];
        if (!empty($detalle->foto_1)) {
            $path = str_starts_with($detalle->foto_1, 'http') ? $detalle->foto_1 : storage_path('app/public/' . $detalle->foto_1);
            if (str_starts_with($detalle->foto_1, 'http') || file_exists($path)) $fotos[] = $path;
        }
        if (!empty($detalle->foto_2)) {
            $path = str_starts_with($detalle->foto_2, 'http') ? $detalle->foto_2 : storage_path('app/public/' . $detalle->foto_2);
            if (str_starts_with($detalle->foto_2, 'http') || file_exists($path)) $fotos[] = $path;
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

    <div class="section-header">Firma</div>

        <div style="margin-top: 80px;"> {{-- Espacio de 3-4 líneas adicionales --}}
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="width: 20%; border: none;"></td>
                    
                    <td style="width: 60%; border: 1px solid #1e293b; padding: 30px 20px; text-align: center; border-radius: 15px; background-color: #f8fafc;">
                        
                        {{-- Espacio para la firma física --}}
                        <div style="height: 70px;"></div>

                        {{-- Línea de firma --}}
                        <div style="width: 80%; border-top: 1.0pt solid #000000; margin: 0 auto 10px auto;"></div>

                        {{-- Datos del Responsable --}}
                        @if(isset($firma_jefe) && $firma_jefe == '1')
                            <div style="text-transform: uppercase; font-size: 11px; color: #000; margin-top: 5px; line-height: 1.2;">
                                {{ $acta->responsable ?? 'JEFE DE ESTABLECIMIENTO' }}
                            </div>
                            <div style="font-size: 10px; color: #334155; margin-top: 6px; line-height: 1.4;">
                                FIRMA DEL JEFE DE ESTABLECIMIENTO <br>
                            </div>
                        @else
                            <div style="text-transform: uppercase; font-size: 11px; color: #000; margin-top: 5px; line-height: 1.2;">
                                {{ $detalle->personal_nombre ?? 'SIN NOMBRE REGISTRADO' }}
                            </div>
                            
                            <div style="font-size: 10px; color: #334155; margin-top: 6px; line-height: 1.4;">
                                DNI: {{ $detalle->personal_dni ?? '________' }} <br>
                            </div> 
                            <div style="font-size: 10px; color: #334155; margin-top: 6px; line-height: 1.4;">
                                {{' FIRMA DEL PROFESIONAL ENTREVISTADO ' }} <br>
                            </div> 
                        @endif 
                    </td>

                    <td style="width: 20%; border: none;"></td>
                </tr>
            </table>
        </div>

<script type="text/php">
    if (isset($pdf)) {
        // Usamos la misma fuente definida en tu body CSS
        $font = $fontMetrics->get_font("helvetica", "normal");
        
        // Tamaño 9 para coincidir con .tab-footer td
        $size = 7;
        
        // Color #94a3b8 exacto
        $color = array(0.58, 0.64, 0.72);
        
        $text = "PÁG {PAGE_NUM} / {PAGE_COUNT}";
        
        // Calculamos el ancho dinámico del texto
        $width = $fontMetrics->get_text_width($text, $font, $size);
        
        // AJUSTES FINALES:
        // X: 565 es el límite derecho para hojas A4 con margen de 1.5cm
        // Y: 813 suele ser el eje exacto para alinearse con el texto de la izquierda
        $pdf->page_text(565 - $width, 813, $text, $font, $size, $color);
    }
</script>
</body>

</html>

