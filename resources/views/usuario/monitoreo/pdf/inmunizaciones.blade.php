<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Módulo 09: Inmunizaciones - Acta {{ $acta->numero_acta }}</title>
    @include('usuario.monitoreo.pdf.partials.premium_style')
</head>
<body>
    {{-- BLOQUE DE CONFIGURACIÓN GLOBAL --}}
    @php 
        $n = 1; // Contador de secciones

        // --------------------------------------------------------
        // PREPARAMOS LOS DATOS DEL PROFESIONAL UNA SOLA VEZ
        // --------------------------------------------------------
        
        // A. Obtener datos crudos
        $rawTipoDoc = $detalle->contenido['profesional']['tipo_doc'] ?? '---';
        $rawNumDoc  = $detalle->contenido['profesional']['doc'] ?? '---';
        
        // B. Aplicar lógica de recorte para C.E. (Quitar los 2 primeros caracteres)
        $docFinal = $rawNumDoc; // Valor por defecto
        
        // C. Preparar Nombre Completo (También lo reutilizaremos)
        $pNom = $detalle->contenido['profesional']['nombres'] ?? '';
        $pPat = $detalle->contenido['profesional']['apellido_paterno'] ?? '';
        $pMat = $detalle->contenido['profesional']['apellido_materno'] ?? '';
        $profNombreCompleto = trim($pPat . ' ' . $pMat . ' ' . $pNom);
        
        if(empty($profNombreCompleto)) {
            $profNombreCompleto = $detalle->contenido['profesional']['apellidos_nombres'] ?? '---';
        }
    @endphp
    <div class="header">
        <h1>Módulo 09: Inmunizaciones</h1>
        <div style="font-weight: bold; color: #64748b; font-size: 10px; margin-top: 5px;">
            ACTA N° {{ str_pad($acta->numero_acta, 3, '0', STR_PAD_LEFT) }} | 
            ESTABLECIMIENTO: {{ $acta->establecimiento->codigo }} - {{ strtoupper($acta->establecimiento->nombre) }} | 
            FECHA: 
            @php
                // 1. Buscamos la fecha específica del módulo
                $fechaRaw = $detalle->contenido['fecha_monitoreo_inmunizaciones'] ?? null;
                
                // 2. Si existe, la formateamos. Si no, usamos la fecha general del acta
                if ($fechaRaw) {
                    echo \Carbon\Carbon::parse($fechaRaw)->format('d/m/Y');
                } else {
                    echo \Carbon\Carbon::parse($acta->fecha)->format('d/m/Y');
                }
            @endphp
        </div>
    </div>

    <div class="section-title">{{ $n++ }}. Detalles del consultorio</div>
    <table>
        <tr>
            <td class="bg-label">Cantidad</td>
            <td class="uppercase">{{ $detalle->contenido['numero_consultorio'] ?? '0' }}</td>
        </tr>
        <tr>
            <td class="bg-label">CONSULTORIO ENTREVISTADO</td>
            <td class="uppercase">{{ $detalle->contenido['denominacion_consultorio'] ?? '---' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Horario de Atención</td>
            <td class="uppercase">
                @php
                    $horarios = $detalle->contenido['horarios'] ?? null;
                    $textoAntiguo = $detalle->contenido['horario_atencion'] ?? null;
                    $seMostroInformacion = false; // Bandera de control
                @endphp

                {{-- CASO 1: Intentamos mostrar el formato NUEVO (Array) --}}
                @if(is_array($horarios) && count($horarios) > 0)
                    @foreach($horarios as $h)
                        {{-- Solo imprimimos si realmente marcaron días en esa fila --}}
                        @if(!empty($h['dias']) && is_array($h['dias']))
                            <div style="margin-bottom: 4px; border-bottom: 1px dashed #e2e8f0; padding-bottom: 2px;">
                                <span style="font-weight: bold; color: #4f46e5;">
                                    {{ implode(', ', $h['dias']) }}
                                </span>
                                <br>
                                <span style="font-size: 9px; color: #64748b;">
                                    {{ $h['inicio'] ?? '--:--' }} - {{ $h['fin'] ?? '--:--' }}
                                </span>
                            </div>
                            @php $seMostroInformacion = true; @endphp
                        @endif
                    @endforeach
                @endif

                {{-- CASO 2: Si no hubo datos nuevos, intentamos mostrar el texto ANTIGUO --}}
                @if(!$seMostroInformacion && !empty($textoAntiguo))
                    {{ $textoAntiguo }}
                    @php $seMostroInformacion = true; @endphp
                @endif

                {{-- CASO 3: Si no hay nada de nada, mostramos los guiones por defecto --}}
                @if(!$seMostroInformacion)
                    ---
                @endif
            </td>
        </tr>
        <tr>
            <td class="bg-label">¿Es un consultorio compartido?</td>
            <td class="uppercase">{{ $detalle->contenido['es_compartido'] ?? '---' }}</td>
        </tr>
        @if(($detalle->contenido['es_compartido'] ?? '') != 'NO')
        <tr>
            <td class="bg-label">¿Con qué servicio/profesional?</td>
            <td class="uppercase">{{ $detalle->contenido['con_quien_comparte'] ?? '---' }}</td>
        </tr>
        @endif
    </table>

    <div class="section-title">{{ $n++ }}. Datos del Profesional Responsable</div>
    <table>
        <tr>
            <td class="bg-label">Nombres y Apellidos</td>
            <td class="uppercase">{{ strtoupper($profNombreCompleto) }}</td>
        </tr>
        <tr>
            <td class="bg-label">Tipo Doc.</td>
            <td>{{ $rawTipoDoc }}</td>
        </tr>
        <tr>
            <td class="bg-label">Documento</td>
            <td>{{ $docFinal }}</td>
        </tr>
        <tr>
            <td class="bg-label">Correo</td>
            <td>{{ $detalle->contenido['profesional']['email'] ?? '---' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Celular</td>
            <td>{{ $detalle->contenido['profesional']['telefono'] ?? '---' }}</td>
        </tr>
        <tr>
            <td class="bg-label">¿Utiliza SIHCE?</td>
            <td class="uppercase">{{ $detalle->contenido['utiliza_sihce'] ?? '---' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Profesion</td>
            <td class="uppercase">{{ $detalle->contenido['profesional']['cargo'] ?? '---' }}</td>
        </tr>
        {{-- DOC ADMIN: Se muestra si SIHCE NO es 'NO' (o sea SI o vacío) --}}
        @if(($detalle->contenido['utiliza_sihce'] ?? '') != 'NO')
            <tr>
                <td class="bg-label">¿Firmó Declaración Jurada?</td>
                <td class="uppercase">{{ $detalle->contenido['firmo_dj'] ?? '---' }}</td>
            </tr>
            <tr>
                <td class="bg-label">¿Firmó Compromiso de Confidencialidad?</td>
                <td class="uppercase">{{ $detalle->contenido['firmo_confidencialidad'] ?? '---' }}</td>
            </tr>
        @endif
    </table>

    @if(($detalle->contenido['profesional']['tipo_doc'] ?? '') == 'DNI')
    <div class="section-title">{{ $n++ }}. DETALLE DE DNI Y FIRMA DIGITAL</div>
    <table>
        <tr>
            <td class="bg-label">Tipo de DNI</td>
            <td class="uppercase">{{ $detalle->contenido['tipo_dni_fisico'] ?? '---' }}</td>
        </tr>
        {{-- Si es AZUL, ocultamos estos campos --}}
        @if(($detalle->contenido['tipo_dni_fisico'] ?? '') != 'AZUL')
        <tr>
            <td class="bg-label">Versión DNIe</td>
            <td class="uppercase">{{ $detalle->contenido['dnie_version'] ?? '---' }}</td>
        </tr>
        <tr>
            <td class="bg-label">¿Firma digitalmente en SIHCE?</td>
            <td class="uppercase">{{ $detalle->contenido['dnie_firma_sihce'] ?? '---' }}</td>
        </tr>
        @endif
        <tr>
            <td class="bg-label">Observaciones/Motivo de Uso</td>
            <td class="uppercase">{{ $detalle->contenido['dni_observacion'] ?? 'SIN OBSERVACIONES' }}</td>
        </tr>
    </table>
    @endif

    {{-- SECCIÓN 4: CAPACITACIÓN (CONDICIONAL SIHCE) --}}
    @if(($detalle->contenido['utiliza_sihce'] ?? '') != 'NO')
    <div class="section-title">{{ $n++ }}. Detalles de Capacitación</div>
    <table>
        <tr>
            <td class="bg-label">¿Recibió Capacitación?</td>
            <td>{{ $detalle->contenido['recibio_capacitacion'] ?? '---' }}</td>
        </tr>
        {{-- Sub-condición: Solo mostrar institución si SÍ recibió capacitación --}}
        @if(($detalle->contenido['recibio_capacitacion'] ?? '') != 'NO')
        <tr>
            <td class="bg-label">¿De parte de quién?</td>
            <td>{{ $detalle->contenido['inst_capacitacion'] ?? '---' }}</td>
        </tr>
        @endif
    </table>
    @endif

    <div class="section-title">{{ $n++ }}. Equipamiento del Consultorio</div>
    @php
        $equipos = \App\Models\EquipoComputo::where('cabecera_monitoreo_id', $acta->id)
                    ->where('modulo', 'inmunizaciones')
                    ->get();
    @endphp
    @if($equipos->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="25%">Descripción</th>
                    <th width="12%">Cantidad</th>
                    <th width="15%">Estado</th>
                    <th width="18%">Propiedad</th>
                    <th width="15%">N.SERIE/C.PAT</th>
                    <th width="15%">Observación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($equipos as $eq)
                <tr>
                    <td class="uppercase">{{ $eq->descripcion }}</td>
                    <td class="text-center">{{ $eq->cantidad }}</td>
                    <td>{{ $eq->estado }}</td>
                    <td>{{ $eq->propio }}</td>
                    <td>{{ $eq->nro_serie ?? '---' }}</td>
                    <td class="uppercase">{{ $eq->observacion ?? '---' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="color: #94a3b8; font-style: italic; padding: 8px;">SIN EQUIPAMIENTO REGISTRADO</div>
    @endif

    {{-- SECCIÓN: CONECTIVIDAD --}}
    <div class="section-title">{{ $n++ }}. CONECTIVIDAD</div>
    @php
        $tipoConectividad  = $detalle->contenido['tipo_conectividad'] ?? null;
        $wifiFuente        = $detalle->contenido['wifi_fuente'] ?? null;
        $operadorServicio  = $detalle->contenido['operador_servicio'] ?? null;
    @endphp
    <table>
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
    </table>

    {{-- SECCIÓN 7: REPORTES (CONDICIONAL SIHCE) --}}
    @if(($detalle->contenido['utiliza_sihce'] ?? '') != 'NO')
    <div class="section-title">{{ $n++ }}. Utilización de Reportes del Sistema</div>
    <table>
        <tr>
            <td class="bg-label">¿Utiliza reportes del Sistema?</td>
            <td>{{ $detalle->contenido['utiliza_reportes'] ?? '---' }}</td>
        </tr>
        @if(($detalle->contenido['utiliza_reportes'] ?? '') != 'NO')
        <tr>
            <td class="bg-label">Si es "SÍ" con quién lo socializa</td>
            <td class="uppercase">{{ $detalle->contenido['socializa_reportes'] ?? '---' }}</td>
        </tr>
        @endif
    </table>
    @endif

    {{-- SECCIÓN 7: SOPORTE (CONDICIONAL SIHCE) --}}
    @if(($detalle->contenido['utiliza_sihce'] ?? '') != 'NO')
    <div class="section-title">{{ $n++ }}. Soporte</div>
    <table>
        <tr>
            <td class="bg-label">¿A quién le comunica?</td>
            <td class="uppercase">{{ $detalle->contenido['comunica_a'] ?? '---' }}</td>
        </tr>
        <tr>
            <td class="bg-label">¿Qué medio utiliza?</td>
            <td>{{ $detalle->contenido['medio_soporte'] ?? '---' }}</td>
        </tr>
    </table>
    @endif

    {{-- 8. COMENTARIOS --}}

    <div class="section-title">{{ $n++ }}. Comentarios</div>
    <div style="border: 1px solid #e2e8f0; padding: 10px; min-height: 40px;" class="uppercase">
        {{ $detalle->contenido['comentarios'] ?? 'SIN COMENTARIOS.' }}
    </div>

    {{-- 9. EVIDENCIA FOTOGRÁFICA --}}
    <div class="section-title">{{ $n++ }}. EVIDENCIA FOTOGRÁFICA</div>

    @php
        $fotos = (!empty($imagenesData) && is_array($imagenesData)) ? $imagenesData : [];
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

    {{-- 10. FIRMAS (Ahora están fuera del IF para que siempre salgan) --}}
    <div class="firma-section">
        <div class="section-title">{{ $n++ }}. Firma del entrevistado</div>
        <div class="firma-container">
            <div class="firma-box">
                <div class="firma-linea"></div>
                <div class="firma-nombre">{{ strtoupper($profNombreCompleto) }}</div>
                <div class="firma-label">{{ $rawTipoDoc }}: {{ $docFinal }}</div>
                <div class="firma-label">FIRMA DEL PROFESIONAL ENTREVISTADO</div>
            </div>
        </div>
    </div>
</body>
</html>
