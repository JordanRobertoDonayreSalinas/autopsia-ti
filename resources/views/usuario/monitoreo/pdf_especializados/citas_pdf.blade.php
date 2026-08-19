<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Citas - CSMC</title>
    @include('usuario.monitoreo.pdf.partials.premium_style')
</head>
<body>

    {{-- INICIALIZAMOS CONTADOR --}}
    @php $i = 1; @endphp

    {{-- ENCABEZADO PRINCIPAL --}}
    <div class="main-header">
        <h1>MÓDULO 02: CITAS</h1>
        <p>
            ACTA N° {{ str_pad($acta->numero_acta, 3, '0', STR_PAD_LEFT) }} | 
            ESTABLECIMIENTO: {{ $acta->establecimiento->codigo ?? 'S/C' }} - {{ $acta->establecimiento->nombre ?? '-' }} |
            FECHA: {{ $dbInicioLabores->fecha_registro ? \Carbon\Carbon::parse($dbInicioLabores->fecha_registro)->format('d/m/Y') : '-' }}
        </p>
    </div>

    {{-- 1. DETALLES DEL CONSULTORIO --}}
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

    {{-- 2. DATOS DEL PROFESIONAL --}}
    <div class="section-header">{{ $i++ }}. DATOS DEL PROFESIONAL</div>
    <table class="details-table">
        <tr>
            <td class="label-cell">APELLIDOS Y NOMBRES:</td>
            <td class="value-cell" colspan="3">
                {{ $profObj->apellido_paterno }} {{ $profObj->apellido_materno }}, {{ $profObj->nombres }}
            </td>
        </tr>
        <tr>
            <td class="label-cell">PROFESION:</td>
            <td class="value-cell">{{ $profObj->cargo ?? '-' }}</td>
            <td class="label-cell">DOCUMENTO:</td>
            <td class="value-cell">{{ $profObj->tipo_doc ?? '' }}: {{ $profObj->doc ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-cell">CORREO:</td>
            <td class="value-cell">{{ $profObj->email ?? '-' }}</td>
            <td class="label-cell">CELULAR:</td>
            <td class="value-cell">{{ $profObj->telefono ?? '-' }}</td>
        </tr>
    </table>

    {{-- 3. DOCUMENTACIÓN ADMINISTRATIVA (NUEVA SECCIÓN) --}}
    <div class="section-header">{{ $i++ }}. DOCUMENTACIÓN ADMINISTRATIVA</div>
    <table class="details-table">
        <tr>
            <td class="label-cell">¿UTILIZA SIHCE?</td>
            <td class="value-cell">{{ $profObj->cuenta_sihce ?? 'NO' }}</td>
            <td class="label-cell">¿FIRMÓ DJ?</td>
            <td class="value-cell">{{ $profObj->firmo_dj ?? 'NO' }}</td>
        </tr>
        <tr>
            <td class="label-cell" colspan="2"></td>
            <td class="label-cell">¿FIRMÓ CONFIDENCIALIDAD?</td>
            <td class="value-cell">{{ $profObj->firmo_confidencialidad ?? 'NO' }}</td>
        </tr>
    </table>

    {{-- 4. CONDICIONAL: DNI (Solo si tipo_doc es DNI) --}}
    @if($profObj->tipo_doc === 'DNI')
        <div class="section-header">{{ $i++ }}. DETALLE DE DNI Y FIRMA DIGITAL</div>
        <table class="details-table">
            <tr>
                <td class="label-cell">TIPO DNI FÍSICO:</td>
                <td class="value-cell">{{ str_replace('_', ' ', $dbDni->tip_dni) }}</td>
                <td class="label-cell">VERSIÓN DNIe:</td>
                <td class="value-cell">{{ $dbDni->version_dni ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-cell">¿FIRMA EN SIHCE?</td>
                <td class="value-cell" colspan="3">{{ $dbDni->firma_sihce ?? 'NO' }}</td>
            </tr>
            @if(!empty($dbDni->comentarios))
            <tr>
                <td class="label-cell">OBSERVACIONES:</td>
                <td class="value-cell" colspan="3">{{ $dbDni->comentarios }}</td>
            </tr>
            @endif
        </table>
    @endif

    {{-- 5. CONDICIONAL: CAPACITACIÓN (Solo si SIHCE es SI) --}}
    @if($profObj->cuenta_sihce === 'SI')
        <div class="section-header">{{ $i++ }}. DETALLES DE CAPACITACIÓN</div>
        <table class="details-table">
            <tr>
                <td class="label-cell">¿RECIBIÓ CAPACITACIÓN?</td>
                <td class="value-cell">{{ $dbCapacitacion->recibieron_cap }}</td>
                <td class="label-cell">ENTIDAD:</td>
                <td class="value-cell">{{ $dbCapacitacion->institucion_cap ?? '-' }}</td>
            </tr>
        </table>
    @endif

    {{-- 6. EQUIPAMIENTO (Siempre visible) --}}
    <div class="section-header">{{ $i++ }}. EQUIPAMIENTO DEL CONSULTORIO</div>
    <table class="inventory-table">
        <thead>
            <tr>
                <th width="35%">DESCRIPCIÓN</th>
                <th width="10%">CANT.</th>
                <th width="15%">ESTADO</th>
                <th width="15%">PROPIEDAD</th>
                <th width="25%">SERIE / COD</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dbInventario as $item)
            <tr>
                <td>{{ $item->descripcion }}</td>
                <td style="text-align: center;">{{ $item->cantidad }}</td>
                <td>{{ $item->estado }}</td>
                <td>{{ $item->propio }}</td>
                <td>{{ $item->nro_serie ?? '-' }}</td>
            </tr>
            @if(!empty($item->observacion))
            <tr>
                <td colspan="5" style="background-color: #f9fafb; font-size: 9px; color: #555; padding-left: 15px;">
                    <i>Obs: {{ $item->observacion }}</i>
                </td>
            </tr>
            @endif
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 10px; color: #777;">Sin equipamiento registrado</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- 7. CONDICIONAL: SOPORTE (Solo si SIHCE es SI) --}}
    @if($profObj->cuenta_sihce === 'SI')
        <div class="section-header">{{ $i++ }}. SOPORTE Y DIFICULTADES</div>
        <table class="details-table">
            <tr>
                <td class="label-cell">INSTITUCIÓN SOPORTE:</td>
                <td class="value-cell">{{ $dbDificultad->insti_comunica ?? '-' }}</td>
                <td class="label-cell">MEDIO COMUNICACIÓN:</td>
                <td class="value-cell">{{ $dbDificultad->medio_comunica ?? '-' }}</td>
            </tr>
        </table>
    @endif

    {{-- 8. COMENTARIOS --}}
    <div class="section-header">{{ $i++ }}. COMENTARIOS GENERALES</div>
    <table class="details-table">
        <tr>
            <td style="padding: 10px; min-height: 40px; vertical-align: top; font-size: 10px; line-height: 1.4;">
                {{ $dbInicioLabores->comentarios ?? 'Sin comentarios registrados.' }}
            </td>
        </tr>
    </table>

    {{-- 9. EVIDENCIA FOTOGRÁFICA (Con Marco) --}}
    <div class="section-header">{{ $i++ }}. EVIDENCIA FOTOGRÁFICA</div>
    @if(!empty($fotoUrl))
        @php
            $isFullUrl = str_starts_with($fotoUrl, 'http');
            $finalPath = $isFullUrl ? $fotoUrl : public_path('storage/' . $fotoUrl);
        @endphp
        @if($isFullUrl || file_exists($finalPath))
            <div class="photo-frame">
                <img src="{{ $finalPath }}">
            </div>
        @else
            <div style="text-align: center; padding: 20px; color: #999; border: 1px dashed #ccc; border-radius: 10px; margin-top: 10px;">
                EVIDENCIA FOTOGRÁFICA NO ENCONTRADA EN EL SERVIDOR
            </div>
        @endif
    @else
        <div style="text-align: center; padding: 20px; color: #999; border: 1px dashed #ccc; border-radius: 10px; margin-top: 10px;">
            NO SE ADJUNTÓ EVIDENCIA FOTOGRÁFICA
        </div>
    @endif

    {{-- 10. FIRMA (Estilo Tarjeta) --}}
    <div class="section-header">{{ $i++ }}. FIRMA</div>
    <div class="signature-container">
        <div class="signature-box">
            {{-- Espacio para firma manuscrita --}}
            <div style="height: 40px;"></div> 
            
            <div class="signature-line"></div>
            
            @if($profObj)
                <div class="signature-name">
                    {{ $profObj->apellido_paterno }} {{ $profObj->apellido_materno }} {{ $profObj->nombres }}
                </div>                
                <div class="signature-dni">
                    {{ $profObj->tipo_doc }}: {{ $profObj->doc }}
                </div>
            @else
                <div class="signature-name">FIRMA PENDIENTE</div>
            @endif
            
            <div class="signature-label">FIRMA DEL PROFESIONAL ENTREVISTADO</div>
        </div>
    </div>

    {{-- PIE DE PÁGINA --}}
    <script type="text/php">
        if (isset($pdf)) {
            $y = $pdf->get_height() - 30;
            $font = $fontMetrics->get_font("helvetica", "normal");
            $size = 8;
            $color = array(0.3, 0.3, 0.3);

            // Texto Izquierdo
            $pdf->page_text(40, $y, "SISTEMA DE MONITOREO - MÓDULO DE CITAS", $font, $size, $color);

            // Texto Derecho (Paginación)
            $text = "PAG: {PAGE_NUM} / {PAGE_COUNT}";
            
            // CORRECCIÓN: Usamos un texto 'dummy' para calcular el ancho real aproximado
            // ya que "{PAGE_NUM}" es mucho más ancho que "1", lo que desalineaba el texto.
            $anchoTexto = $fontMetrics->get_text_width("PAG: 88 / 88", $font, $size);
            
            // 42 puntos es aprox 1.5cm (tu margen derecho), así queda alineado con el contenido
            $x = $pdf->get_width() - $anchoTexto - 42; 
            
            $pdf->page_text($x, $y, $text, $font, $size, $color);
        }
    </script>

</body>
</html>