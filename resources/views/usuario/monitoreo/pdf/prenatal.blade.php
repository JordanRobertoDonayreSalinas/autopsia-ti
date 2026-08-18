<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Módulo 10: Atención Prenatal - Acta {{ $acta->numero_acta }}</title>
    @include('usuario.monitoreo.pdf.partials.premium_style')
</head>

<body>
    <div class="footer-frame">
        SISTEMA DE ACTAS
    </div>

    <div class="header">
        <h1>Módulo 10: ATENCIÓN PRENATAL</h1>
        <div class="header-sub">
            ACTA N° {{ str_pad($acta->numero_acta, 3, '0', STR_PAD_LEFT) }} |
            ESTABLECIMIENTO: {{ $acta->establecimiento->codigo ?? '-' }} -
            {{ $acta->establecimiento->nombre ?? 'NO ESPECIFICADO' }} |
            FECHA: {{ \Carbon\Carbon::parse($acta->fecha ?? ($registro->fecha_registro ?? now()))->format('d/m/Y') }}
        </div>
    </div>

    @php $n = 1; @endphp

    <div class="section-title">{{ $n++ }}. DETALLES DEL CONSULTORIO</div>
    <table>
        <tr>
            <td class="bg-label">NRO. CONSULTORIOS</td>
            <td>{{ $registro->nro_consultorios ?? '0' }}</td>
        </tr>
        <tr>
            <td class="bg-label">NOMBRE DEL CONSULTORIO</td>
            <td>{{ $registro->nombre_consultorio ?? '-' }}</td>
        </tr>
    </table>

    {{-- SECCIÓN 1: DATOS GENERALES Y PROFESIONAL --}}
    <div class="section-title">{{ $n++ }}. DATOS DEL PROFESIONAL</div>
    <table>
        <tr>
            <td class="bg-label">APELLIDOS Y NOMBRES</td>
            <td>{{ $registro->personal_nombre ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bg-label">TIPO DOC.</td>
            <td>{{ $registro->personal_tipo_doc ?? 'DNI' }}</td>
        </tr>
        <tr>
            <td class="bg-label">DOCUMENTO</td>
            <td>{{ $registro->personal_dni ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bg-label">CARGO</td>
            <td>{{ $registro->personal_especialidad ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bg-label">CORREO ELECTRÓNICO</td>
            <td style="text-transform: lowercase;">{{ $registro->personal_correo ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bg-label">CELULAR</td>
            <td>{{ $registro->personal_celular ?? '-' }}</td>
        </tr>
        <tr>
            <td class="bg-label">¿UTILIZA SIHCE?</td>
            <td>
                @if (($registro->utiliza_sihce ?? 'NO') == 'SI')
                    <span class="status-ok">SÍ UTILIZA</span>
                @else
                    <span class="status-err">NO UTILIZA</span>
                @endif
            </td>
        </tr>
        @if (($registro->utiliza_sihce ?? 'NO') == 'SI')
            <tr>
                <td class="bg-label">¿FIRMÓ DECLARACIÓN JURADA?</td>
                <td>{{ $registro->firma_dj ?? '-' }}</td>
            </tr>
            <tr>
                <td class="bg-label">¿FIRMÓ CONFIDENCIALIDAD?</td>
                <td>{{ $registro->firma_confidencialidad ?? '-' }}</td>
            </tr>
        @endif
    </table>

    {{-- SECCIÓN 2: DOCUMENTACIÓN Y ACCESOS --}}
    @if (($registro->personal_tipo_doc ?? '') == 'DNI' || ($registro->personal_tipo_doc ?? '') == 'DNIe')
        <div class="section-title">{{ $n++ }}. DETALLE DE DNI Y FIRMA DIGITAL</div>
        <table>

            <tr>
                <td class="bg-label">TIPO DNI FÍSICO</td>
                <td>{{ $registro->tipo_dni_fisico ?? '-' }}</td>
            </tr>
            @if (($registro->tipo_dni_fisico ?? '') == 'ELECTRONICO')
                <tr>
                    <td class="bg-label">DETALLE DNI ELECTRÓNICO</td>
                    <td>
                        VERSIÓN: <b>{{ $registro->dnie_version ?? '-' }}</b> |
                        FIRMA EN SIHCE: <b>{{ $registro->firma_sihce ?? '-' }}</b>
                    </td>
                </tr>
            @endif
        </table>
    @endif

    {{-- SECCIÓN 3: CAPACITACIÓN --}}
    <div class="section-title">{{ $n++ }}. DETALLES DE CAPACITACION</div>
    <table>
        <tr>
            <td class="bg-label">¿RECIBIÓ CAPACITACIÓN?</td>
            <td>
                @if (($registro->capacitacion_recibida ?? '') == 'SI')
                    <span class="status-ok">SÍ</span>
                @else
                    <span class="status-warn">{{ $registro->capacitacion_recibida ?? '-' }}</span>
                @endif
            </td>
        </tr>
        @if (($registro->capacitacion_recibida ?? '') == 'SI')
            <tr>
                <td class="bg-label">ENTIDAD CAPACITADORA</td>
                <td>{{ $registro->capacitacion_entes ?? '-' }}</td>
            </tr>
        @endif
    </table>

    {{-- SECCIÓN 4: MATERIALES --}}
    <div class="section-title">{{ $n++ }}. MATERIALES</div>
    <table>
        <tr>
            <td class="bg-label">AL INICIAR LABORES CUENTA CON:</td>
            <td>
                @if (!empty($registro->insumos_disponibles))
                    {{ implode(', ', $registro->insumos_disponibles) }}
                @else
                    <span class="status-err">NO SE REGISTRARON INSUMOS</span>
                @endif
            </td>
        </tr>
    </table>

    {{-- SECCIÓN 5: EQUIPAMIENTO --}}
    <div class="section-title">{{ $n++ }}. EQUIPAMIENTO INFORMÁTICO</div>
    <table>
        <thead>
            <tr>
                <th width="30%">DESCRIPCIÓN</th>
                <th width="10%">CANTIDAD</th>
                <th width="15%">ESTADO</th>
                <th width="15%">PROPIEDAD</th>
                <th width="15%">N.SERIE/C.PAT</th>
                <th width="15%">OBSERVACION</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $equiposDbFallback = \App\Models\EquipoComputo::where('cabecera_monitoreo_id', $acta->id)->where('modulo', 'atencion_prenatal')->get();
                $equiposGuardados = [];
                if ($equiposDbFallback->isNotEmpty()) {
                    foreach($equiposDbFallback as $eq) {
                        $equiposGuardados[] = [
                            'nombre' => $eq->descripcion,
                            'serie' => $eq->nro_serie,
                            'propiedad' => $eq->propio,
                            'estado' => $eq->estado,
                            'observaciones' => $eq->observacion,
                        ];
                    }
                } else {
                    $equiposGuardados = $registro->equipos_listado ?? [];
                }
            @endphp
            @forelse($equiposGuardados as $eq)
                <tr>
                    <td>{{ $eq['nombre'] ?? '-' }}</td>
                    <td class="text-center">{{ 1 }}</td>
                    <td class="text-center">
                        @php
                            $est = strtoupper($eq['estado'] ?? '');
                            $clase =
                                $est == 'OPERATIVO' || $est == 'BUENO'
                                    ? 'status-ok'
                                    : ($est == 'REGULAR'
                                        ? 'status-warn'
                                        : 'status-err');
                        @endphp
                        <span class="{{ $clase }}">{{ $est ?: '-' }}</span>
                    </td>
                    <td class="text-center">{{ $eq['propiedad'] ?? '-' }}</td>
                    <td>{{ $eq['serie'] ?? '-' }}</td>
                    <td>{{ $eq['observaciones'] ?? '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">NO SE REGISTRARON EQUIPOS.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if ($registro->equipos_observaciones)
        <table>
            <tr>
                <td class="bg-label">OBSERVACIONES ADICIONALES</td>
                <td>{{ $registro->equipos_observaciones }}</td>
            </tr>
        </table>
    @endif

    {{-- SECCIÓN: CONECTIVIDAD --}}
    @php
        $tipoConectividad = $registro->tipo_conectividad ?? null;
        $wifiFuente       = $registro->wifi_fuente ?? null;
        $operadorServicio = $registro->operador_servicio ?? null;
    @endphp
    <div class="section-title">{{ $n++ }}. CONECTIVIDAD</div>
    <table>
        <tr>
            <td class="bg-label">TIPO DE CONECTIVIDAD</td>
            <td>{{ $tipoConectividad ?? '---' }}</td>
        </tr>
        @if($tipoConectividad == 'WIFI')
        <tr>
            <td class="bg-label">FUENTE DE WIFI</td>
            <td>{{ $wifiFuente ?? '---' }}</td>
        </tr>
        @endif
        @if($tipoConectividad != 'SIN CONECTIVIDAD')
        <tr>
            <td class="bg-label">OPERADOR DE SERVICIO</td>
            <td>{{ $operadorServicio ?? '---' }}</td>
        </tr>
        @endif
        @php
            $v_desc = $registro->velocidad_descarga ?? $registro->velocidad_internet_cantidad ?? null;
            $v_sub  = $registro->velocidad_subida ?? null;
            $v_desc_uni = $registro->velocidad_descarga_unidad ?? $registro->velocidad_internet_unidad ?? 'Mbps';
            $v_sub_uni = $registro->velocidad_subida_unidad ?? $registro->velocidad_internet_unidad ?? 'Mbps';
        @endphp
        @if($tipoConectividad != 'SIN CONECTIVIDAD' && ($v_desc || $v_sub))
        <tr>
            
            <td class="bg-label">Velocidad Descarga</td>
            <td>{{ $v_desc ? $v_desc . ' ' . $v_desc_uni : '---' }}</td>
        </tr>
        <tr>
            
            <td class="bg-label">Velocidad Subida</td>
            <td>{{ $v_sub ? $v_sub . ' ' . $v_sub_uni : '---' }}</td>
        </tr>
        
        @endif
    </table>

    {{-- SECCIÓN 6: DATOS DE GESTIÓN --}}
    <div class="section-title">{{ $n++ }}. DATOS DE GESTIÓN</div>
    <table>
        <tr>
            <td class="bg-label">GESTANTES REGISTRADAS (MES)</td>
            <td>{{ $registro->nro_gestantes_mes ?? '0' }}</td>
        </tr>
        <tr>
            <td class="bg-label">¿REALIZA DESCARGA HISMINSA?</td>
            <td>
                @if (($registro->gestion_hisminsa ?? '') == 'SI')
                    <span class="status-ok">SÍ</span>
                @else
                    <span class="status-err">NO</span>
                @endif
            </td>
        </tr>
        @if (($registro->gestion_reportes ?? '') == 'SI')
            <tr>
                <td class="bg-label">¿UTILIZA REPORTES?</td>
                <td>
                    {{ $registro->gestion_reportes ?? '-' }}

                    (Socializa con: {{ $registro->gestion_reportes_socializa ?? '-' }})

                </td>
            </tr>
        @endif
    </table>

    {{-- SECCIÓN 7: SOPORTE Y DIFICULTADES --}}
    @if (($registro->utiliza_sihce ?? '') == 'SI')
        <div class="section-title">{{ $n++ }}. SOPORTE</div>
        <table>
            <tr>
                <td class="bg-label">ANTE DIFICULTADES COMUNICA A:</td>
                <td>{{ $registro->dificultad_comunica_a ?? '-' }}</td>
            </tr>
            <tr>
                <td class="bg-label">MEDIO DE COMUNICACIÓN:</td>
                <td>{{ $registro->dificultad_medio_uso ?? '-' }}</td>
            </tr>
        </table>
    @endif

    {{-- SECCIÓN 8: EVIDENCIA --}}
    <div class="section-title">{{ $n++ }}. EVIDENCIA FOTOGRÁFICA</div>
    @php
        $fotos = is_string($registro->fotos_evidencia) ? json_decode($registro->fotos_evidencia, true) : ($registro->fotos_evidencia ?? []);
        $cantidad = is_array($fotos) ? count($fotos) : 0;
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

    {{-- FIRMAS --}}
    <div class="firma-section">
        <div class="section-title">{{ $n++ }}. CONFORMIDAD</div>
        <br>
        <div class="firma-container">
            <div class="firma-linea">
                @if ($registro->firma_grafica)
                    <img src="{{ $registro->firma_grafica }}" style="height: 70px; margin-top: -40px;">
                @endif
            </div>
            <div class="firma-nombre">{{ $registro->personal_nombre ?? '___________________' }}</div>
            <div class="firma-cargo">
                {{ $registro->personal_tipo_doc ?? 'DOC' }}: {{ $registro->personal_dni ?? '________' }}
                <br>
                {{ $registro->personal_cargo ?? 'FIRMA DEL PROFESIONAL ENTREVISTADO' }}
            </div>
        </div>
    </div>

</body>

</html>
