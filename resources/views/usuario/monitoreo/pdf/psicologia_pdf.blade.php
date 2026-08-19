<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Módulo 07: Psicología - Acta {{ $acta->numero_acta }}</title>
    @include('usuario.monitoreo.pdf.partials.premium_style')
</head>
<body>

    {{-- INICIALIZAR CONTADOR --}}
    @php $i = 1; @endphp

    <div class="main-header">
        <h1>Módulo 07: CONSULTA EXTERNA - Psicología</h1>
        <p>
            ACTA N° {{ str_pad($acta->numero_acta, 3, '0', STR_PAD_LEFT) }} | 
            ESTABLECIMIENTO: {{ $acta->establecimiento->codigo ?? 'S/C' }} - {{ $acta->establecimiento->nombre ?? '-' }} |
            FECHA: {{ $dbInicioLabores->fecha_registro ? \Carbon\Carbon::parse($dbInicioLabores->fecha_registro)->format('d/m/Y') : '-' }}
        </p>
    </div>

    {{-- 1. DETALLES --}}
    <div class="section-header">{{ $i++ }}. DETALLES DEL CONSULTORIO</div>
    <table class="details-table">
        <tr>
            <td class="label-cell">CANTIDAD CONSULTORIOS:</td>
            <td class="value-cell" colspan="3">{{ $dbInicioLabores->cant_consultorios ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-cell">CONSULTORIO ENTREVISTADO:</td>
            <td class="value-cell" colspan="3">{{ $dbInicioLabores->nombre_consultorio ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-cell">TURNO:</td> 
            <td class="value-cell" colspan="3">{{ $dbInicioLabores->turno ?? '-' }}</td> 
        </tr>
    </table>

    {{-- 2. PROFESIONAL --}}
    <div class="section-header">{{ $i++ }}. DATOS DEL PROFESIONAL</div>
    @php 
        $prof = $dbCapacitacion->profesional ?? null; 
    @endphp
    <table class="details-table">
        <tr>
            <td class="label-cell">APELLIDOS Y NOMBRES:</td>
            <td class="value-cell">
                {{ $prof ? "$prof->apellido_paterno $prof->apellido_materno, $prof->nombres" : 'NO REGISTRADO' }}
            </td>
            <td class="label-cell">CARGO:</td>
            <td class="value-cell">{{ $prof->cargo ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-cell">TIPO DOC:</td>
            <td class="value-cell">{{ $prof->tipo_doc ?? '-' }}</td>
            <td class="label-cell">¿FIRMÓ DECLARACIÓN JURADA?</td>
            <td class="value-cell">{{ $dbCapacitacion->decl_jurada ?? 'NO' }}</td>
        </tr>
        <tr>
            <td class="label-cell">DOCUMENTO:</td>
            <td class="value-cell">{{ $prof->doc ?? '-' }}</td>
            <td class="label-cell">¿FIRMÓ COMP. CONFIDENCIALIDAD?</td>
            <td class="value-cell">{{ $dbCapacitacion->comp_confidencialidad ?? 'NO' }}</td>
        </tr>
        <tr>
            <td class="label-cell">CORREO:</td>
            <td class="value-cell">{{ $prof->email ?? '-' }}</td>
            <td class="label-cell">CELULAR:</td>
            <td class="value-cell">{{ $prof->telefono ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-cell" colspan="2"></td>
            <td class="label-cell">¿UTILIZA SIHCE?</td>
            <td class="value-cell">{{ $dbInicioLabores->utiliza_sihce ?? '-' }}</td>
        </tr>
    </table>

    {{-- 3. DNI (CONDICIONAL) --}}
    @if(isset($prof->tipo_doc) && $prof->tipo_doc === 'DNI')
        <div class="section-header">{{ $i++ }}. DETALLE DE DNI Y FIRMA DIGITAL</div>
        <table class="details-table">
            <tr>
                <td class="label-cell">TIPO DNI:</td>
                <td class="value-cell">{{ str_replace('_', ' ', $dbDni->tip_dni ?? '-') }}</td>
                <td class="label-cell">VERSIÓN DNIe:</td>
                <td class="value-cell">{{ $dbDni->version_dni ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-cell">¿REALIZA FIRMA EN SIHCE?</td>
                <td class="value-cell" colspan="3">{{ $dbDni->firma_sihce ?? 'NO' }}</td>
            </tr>
            <tr>
                <td class="label-cell">OBSERVACIONES DNI:</td>
                <td class="value-cell" colspan="3">{{ $dbDni->comentarios ?? '-' }}</td>
            </tr>
        </table>
    @endif

    {{-- 4. CAPACITACIÓN (CONDICIONAL) --}}
    @if(isset($dbInicioLabores->utiliza_sihce) && $dbInicioLabores->utiliza_sihce === 'SI')
        <div class="section-header">{{ $i++ }}. DETALLES DE CAPACITACIÓN</div>
        <table class="details-table">
            <tr>
                <td class="label-cell">¿RECIBIÓ CAPACITACIÓN?</td>
                <td class="value-cell">{{ $dbCapacitacion->recibieron_cap ?? '-' }}</td>
                <td class="label-cell">ENTIDAD CAPACITADORA:</td>
                <td class="value-cell">{{ $dbCapacitacion->institucion_cap ?? 'N/A' }}</td>
            </tr>
        </table>
    @endif

    {{-- 5. MATERIALES --}}
    <div class="section-header">{{ $i++ }}. MATERIALES (INICIO DE LABORES)</div>
    <table class="details-table">
        <tr>
            <td class="label-cell">TIPO FUA:</td>
            <td class="value-cell">{{ str_replace('_', ' ', $dbInicioLabores->fua ?? '-') }}</td>
            <td class="label-cell">TIPO REFERENCIA:</td>
            <td class="value-cell">{{ $dbInicioLabores->referencia ?? '-' }}</td>
        </tr>
    </table>

    {{-- 6. EQUIPAMIENTO --}}
    <div class="section-header">{{ $i++ }}. EQUIPAMIENTO DEL CONSULTORIO</div>
    <table class="inventory-table">
        <thead>
            <tr>
                <th width="30%">DESCRIPCIÓN</th>
                <th width="10%">CANTIDAD</th>
                <th width="15%">ESTADO</th>
                <th width="15%">PROPIEDAD</th>
                <th width="15%">N.SERIE / C.PAT</th>
                <th width="15%">OBSERVACIÓN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dbInventario as $item)
            <tr>
                <td>{{ $item->descripcion }}</td>
                <td style="text-align: center;">{{ $item->cantidad ?? '1' }}</td>
                <td>{{ $item->estado }}</td>
                <td>{{ $item->propio }}</td>
                <td>{{ $item->nro_serie ?? '-' }}</td>
                <td>{{ $item->observacion }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 10px; color: #777;">Sin equipamiento registrado</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- SECCIÓN CONECTIVIDAD --}}
    @php
        $tipoConectividad = $dbConectividad->tipo_conectividad ?? null;
        $wifiFuente       = $dbConectividad->wifi_fuente ?? null;
        $operadorServicio = $dbConectividad->operador_servicio ?? null;
    @endphp
    <div class="section-header">{{ $i++ }}. CONECTIVIDAD</div>
    <table class="details-table">
        <tr>
            <td class="label-cell">TIPO DE CONECTIVIDAD:</td>
            <td class="value-cell" colspan="3">{{ $tipoConectividad ?? '---' }}</td>
        </tr>
        @if($tipoConectividad == 'WIFI')
        <tr>
            <td class="label-cell">FUENTE DE WIFI:</td>
            <td class="value-cell" colspan="3">{{ $wifiFuente ?? '---' }}</td>
        </tr>
        @endif
        @if($tipoConectividad != 'SIN CONECTIVIDAD')
        <tr>
            <td class="label-cell">OPERADOR DE SERVICIO:</td>
            <td class="value-cell" colspan="3">{{ $operadorServicio ?? '---' }}</td>
        </tr>
        @endif
        @php
            $v_desc = $dbConectividad->velocidad_descarga ?? $dbConectividad->velocidad_internet_cantidad ?? null;
            $v_sub  = $dbConectividad->velocidad_subida ?? null;
            $v_desc_uni = $dbConectividad->velocidad_descarga_unidad ?? $dbConectividad->velocidad_internet_unidad ?? 'Mbps';
            $v_sub_uni = $dbConectividad->velocidad_subida_unidad ?? $dbConectividad->velocidad_internet_unidad ?? 'Mbps';
        @endphp
        @if($tipoConectividad != 'SIN CONECTIVIDAD' && ($v_desc || $v_sub))
        <tr>
            
            <td class="label-cell">VELOCIDAD DESCARGA:</td>
            <td class="value-cell" colspan="3">{{ $v_desc ? $v_desc . ' ' . $v_desc_uni : '---' }}</td>
        </tr>
        <tr>
            
            <td class="label-cell">VELOCIDAD SUBIDA:</td>
            <td class="value-cell" colspan="3">{{ $v_sub ? $v_sub . ' ' . $v_sub_uni : '---' }}</td>
        </tr>
        
        @endif
    </table>

    {{-- 7. SOPORTE (CONDICIONAL) --}}
    @if(isset($dbInicioLabores->utiliza_sihce) && $dbInicioLabores->utiliza_sihce === 'SI')
        <div class="section-header">{{ $i++ }}. SOPORTE</div>
        <table class="details-table">
            <tr>
                <td class="label-cell">INSTITUCIÓN QUE COORDINA:</td>
                <td class="value-cell">{{ $dbDificultad->insti_comunica ?? '-' }}</td>
                <td class="label-cell">MEDIO DE COMUNICACIÓN:</td>
                <td class="value-cell">{{ $dbDificultad->medio_comunica ?? '-' }}</td>
            </tr>
        </table>
    @endif

    {{-- 8. COMENTARIOS --}}
    <div class="section-header">{{ $i++ }}. COMENTARIOS GENERALES</div>
    <table class="details-table">
        <tr>
            <td style="padding: 10px; height: 40px; vertical-align: top;">
                {{ $dbInicioLabores->comentarios ?? 'Sin comentarios generales registrados.' }}
            </td>
        </tr>
    </table>

    {{-- 9. EVIDENCIA FOTOGRÁFICA --}}
    <div class="section-header">{{ $i++ }}. EVIDENCIA FOTOGRÁFICA</div>

    @php
        $fotosFinales = [];
        foreach ($dbFotos as $foto) {
            $url = $foto->url_foto;
            $isFullUrl = str_starts_with($url, 'http');
            $realPath = $isFullUrl ? $url : public_path('storage/' . $url);
            if ($isFullUrl || file_exists($realPath)) {
                $fotosFinales[] = $realPath;
            }
        }
        $cantidad = count($fotosFinales);
    @endphp

    @if ($cantidad > 0)

        @if ($cantidad == 1)
        <div style="width: 100%; text-align: center; margin-top: 15px;">
            <div style="display: inline-block; border: 1px solid #e2e8f0; padding: 5px; background: #fff; border-radius: 10px;">
                <img src="{{ $fotosFinales[0] }}" style="width: 100%; height: 250px; object-fit: cover; border-radius: 8px;">
            </div>
        </div>
        @else
        <table style="width: 100%; border: none; margin-top: 10px;">
            <tr>
                @foreach ($fotosFinales as $index => $fotoUrl)
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

    {{-- 10. FIRMA --}}
    <div class="section-header">{{ $i++ }}. FIRMA </div>
    
    <div class="signature-section">
        <div class="signature-frame">
            <div class="signature-box">
                @if($prof)
                    <div style="font-weight: bold; font-size: 11px; color: #1e293b;">
                        {{ $prof->apellido_paterno }} {{ $prof->apellido_materno }} {{ $prof->nombres }}
                    </div>                
                    <div style="font-size: 10px; color: #64748b; margin-top: 1px;">
                        {{ $prof->tipo_doc }}: {{ $prof->doc }}
                    </div>
                    <div style="font-weight: bold; font-size: 9px; margin-top: 4px;">FIRMA DEL PROFESIONAL ENTREVISTADO</div>
                @else
                    <div style="padding: 10px;">FIRMA PENDIENTE</div>
                @endif
            </div>
        </div>
    </div>

    {{-- SCRIPT --}}
    <script type="text/php">
        if (isset($pdf)) {
            $y = $pdf->get_height() - 30;
            $font = $fontMetrics->get_font("helvetica", "normal");
            $size = 8;
            $color = array(0.3, 0.3, 0.3);

            $pdf->page_text(40, $y, "SISTEMA DE ACTAS", $font, $size, $color);

            $text = "PAG: {PAGE_NUM} / {PAGE_COUNT}";
            $dummyText = "PAG: 10 / 10"; 
            $width = $fontMetrics->get_text_width($dummyText, $font, $size);
            $x = $pdf->get_width() - $width - 30;
            
            $pdf->page_text($x, $y, $text, $font, $size, $color);
        }
    </script>

</body>
</html>