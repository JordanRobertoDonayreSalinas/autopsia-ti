<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Módulo 03: Triaje - Acta {{ $acta->numero_acta }}</title>
    <style>
        /* --- CONFIGURACIÓN GENERAL --- */
        body { 
            font-family: 'Helvetica', sans-serif; 
            font-size: 10px; 
            color: #333; 
            margin: 0; 
            padding-top: 0;
        }

        @page {
            margin: 1cm 1.5cm 1.5cm 1.5cm; 
        }

        /* --- ENCABEZADO SUPERIOR --- */
        .main-header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #4f46e5; 
            padding-bottom: 10px; 
        }
        .main-header h1 { 
            color: #4f46e5; 
            margin: 0; 
            font-size: 16px; 
            text-transform: uppercase; 
        }
        .main-header p { 
            margin: 3px 0; 
            font-size: 10px; 
            color: #555; 
        }

        /* --- TÍTULOS DE SECCIÓN --- */
        .section-header {
            background-color: #f3f4f6; 
            border-left: 5px solid #4f46e5; 
            padding: 6px 10px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            color: #1f2937;
            margin-top: 15px;
            margin-bottom: 0; 
        }

        /* --- TABLAS --- */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .details-table td, .details-table th {
            border: 1px solid #e5e7eb; 
            padding: 6px 8px;
            vertical-align: middle;
        }
        .label-cell {
            background-color: #ffffff;
            font-weight: bold;
            color: #374151; 
            width: 25%; 
        }
        .value-cell {
            color: #000;
        }

        /* --- TABLA INVENTARIO --- */
        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }
        .inventory-table th {
            background-color: #4f46e5;
            color: white;
            padding: 5px;
            font-size: 9px;
            text-align: left;
            text-transform: uppercase;
        }
        .inventory-table td {
            border: 1px solid #e5e7eb;
            padding: 5px;
            font-size: 9px;
        }

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

        /* --- ESTANDARIZACIÓN DE FIRMAS --- */
        .signature-section {
            margin-top: 30px;
            width: 100%;
        }
        .signature-container {
            width: 380px;
            margin: 0 auto;
        }
        .signature-box {
            border: 1px solid #cbd5e1;
            border-radius: 15px;
            padding: 20px;
            padding-top: 100px;
            text-align: center;
            background-color: #f8fafc;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin: 10px auto 5px auto;
            width: 85%;
        }
        .signature-name {
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            color: #1e293b;
        }
        .signature-label {
            font-size: 9px;
            color: #475569;
            margin-top: 2px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    {{-- INICIALIZAMOS CONTADOR DE SECCIONES --}}
    @php $i = 1; @endphp

    <div class="main-header">
        <h1>Módulo 03: TRIAJE</h1>
        <p>
            ACTA N° {{ str_pad($acta->numero_acta, 3, '0', STR_PAD_LEFT) }} | 
            ESTABLECIMIENTO: {{ $acta->establecimiento->codigo ?? 'S/C' }} - {{ $acta->establecimiento->nombre ?? '-' }} |
            FECHA: {{ \Carbon\Carbon::parse($acta->fecha ?? ($dbInicioLabores->fecha_registro ?? now()))->format('d/m/Y') }}
        </p>
    </div>

    {{-- SECCIÓN 1 (Siempre visible) --}}
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

    {{-- SECCIÓN 2 (Siempre visible) --}}
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

    {{-- SECCIÓN CONDICIONAL: DNI (Solo si es DNI) --}}
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

    {{-- SECCIÓN CONDICIONAL: CAPACITACIÓN (Solo si utiliza SIHCE = SI) --}}
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

    {{-- SECCIÓN EQUIPAMIENTO (Siempre visible) --}}
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

    {{-- SECCIÓN CONDICIONAL: SOPORTE (Solo si utiliza SIHCE = SI) --}}
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

    {{-- SECCIÓN COMENTARIOS (Siempre visible) --}}
    <div class="section-header">{{ $i++ }}. COMENTARIOS GENERALES</div>
    <table class="details-table">
        <tr>
            <td style="padding: 10px; height: 40px; vertical-align: top;">
                {{ $dbInicioLabores->comentarios ?? 'Sin comentarios generales registrados.' }}
            </td>
        </tr>
    </table>

    {{-- SECCIÓN EVIDENCIA (Siempre visible) --}}
    <div class="section-header">{{ $i++ }}. EVIDENCIA FOTOGRÁFICA</div>

    @php
        // Construir array de rutas válidas
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

    {{-- SECCIÓN FIRMA (Siempre visible) --}}
    <div class="section-header">{{ $i++ }}. FIRMA</div>
    
    <div class="signature-section">
        <div class="signature-container">
            <div class="signature-box">
                @if($prof)
                    @include('usuario.firmas.pdf_stamp', ['firma' => $firmaEntrevistado])
                    
                    <div class="signature-line"></div>
                    @if(isset($firma_jefe) && $firma_jefe == '1')
                        <div class="signature-name">{{ $acta->responsable ?? 'JEFE DE ESTABLECIMIENTO' }}</div>
                        <div class="signature-label">FIRMA DEL JEFE DE ESTABLECIMIENTO</div>
                    @else
                        <div class="signature-name">{{ $prof->apellido_paterno }} {{ $prof->apellido_materno }} {{ $prof->nombres }}</div>                
                        <div class="signature-label">{{ $prof->tipo_doc }}: {{ $prof->doc }}</div>
                        <div class="signature-label">FIRMA DEL PROFESIONAL ENTREVISTADO</div>
                    @endif
                @else
                    <div style="padding: 40px; color: #94a3b8; font-style: italic;">FIRMA PENDIENTE</div>
                @endif
            </div>
        </div>
    </div>

    {{-- SCRIPT PIE DE PÁGINA --}}
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