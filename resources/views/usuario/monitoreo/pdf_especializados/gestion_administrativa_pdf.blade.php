<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión Administrativa Especializada - Acta {{ $monitoreo->numero_acta }}</title>
    <style>
        /* MARGENES DE PÁGINA */
        @page { margin: 1cm 1.5cm 2cm 1.5cm; }
        
        /* TIPOGRAFÍA GLOBAL */
        body { 
            font-family: 'Helvetica', sans-serif; 
            font-size: 10px; 
            color: #334155; /* Slate 700 */
            line-height: 1.5; 
        }

        /* ENCABEZADO */
        .header { 
            text-align: center; 
            margin-bottom: 25px; 
            border-bottom: 2px solid #0f766e; /* Teal 700 to match web view */
            padding-bottom: 15px; 
        }
        .header h1 { 
            font-family: 'Helvetica', sans-serif !important;
            font-weight: bold !important;
            margin: 0 0 5px 0; 
            font-size: 18px; 
            text-transform: uppercase; 
            color: #0f766e !important; /* Color Teal forzado */
            letter-spacing: -0.5px;
        }
        .header-meta {
            font-family: 'Helvetica', sans-serif !important;
            font-weight: bold !important;
            color: #64748b !important;
            font-size: 9px; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* TÍTULOS DE SECCIÓN */
        .section-title { 
            background-color: #f0fdfa; /* Teal 50 */
            color: #0f766e; /* Teal 700 */
            padding: 8px 12px; 
            font-weight: bold; 
            text-transform: uppercase; 
            border-left: 4px solid #14b8a6; /* Teal 500 */
            border-radius: 4px;
            margin-top: 20px; 
            margin-bottom: 10px; 
            font-size: 11px; 
            letter-spacing: 0.5px;
        }

        /* TABLAS */
        table { 
            width: 100%; 
            border-collapse: separate; 
            border-spacing: 0; 
            margin-bottom: 10px; 
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        th, td { 
            padding: 8px 10px; 
            text-align: left; 
            vertical-align: middle; 
            word-wrap: break-word;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
        }
        /* Eliminar bordes duplicados */
        tr:last-child td { border-bottom: none; }
        th:last-child, td:last-child { border-right: none; }

        th { 
            background-color: #f1f5f9; /* Slate 100 */
            color: #475569; /* Slate 600 */
            font-size: 9px; 
            font-weight: 700;
            text-transform: uppercase; 
        }

        /* ESTILOS PARA CELDAS TIPO ETIQUETA (Izquierda) */
        .bg-label { 
            background-color: #f8fafc; /* Slate 50 */
            color: #64748b;
            font-weight: 700; 
            width: 35%;  
            text-transform: uppercase;
            font-size: 9px;
        }

        .uppercase { text-transform: uppercase; }
        .text-center { text-align: center; }

        /* Evidencia fotográfica */
        .foto-container { 
            margin: 20px auto; 
            padding: 8px; 
            border: 1px solid #cbd5e1; 
            background-color: #fff; 
            border-radius: 8px;
            text-align: center; 
            display: table;
            width: auto;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .foto { 
            display: block; 
            margin: 0 auto; 
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
        }

        /* Grid de fotos */
        .foto-grid { 
            display: flex; 
            flex-wrap: wrap; 
            justify-content: space-between; 
            gap: 10px; 
            margin: 15px 0; 
            padding: 15px; 
            border: 1px solid #cbd5e1; 
            background-color: #f8fafc; 
            border-radius: 12px;
        }

        /* Firmas */
        .firma-section { margin-top: 30px; page-break-inside: avoid; }
        .firma-container { width: 60%; margin: 0 auto; }
        .firma-box { 
            text-align: center; 
            padding: 0 35px; 
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }
        .firma-linea { 
            border-bottom: 1px solid #94a3b8; 
            height: 120px; 
            margin-bottom: 10px; 
            width: 100%;
        }
        .firma-label { font-size: 9px; color: #64748b; margin: 2px 0; }
        .firma-nombre { 
            font-family: 'Helvetica', sans-serif !important;
            font-weight: bold !important;
            text-transform: uppercase; 
            font-size: 11px; 
            color: #1e293b !important;
            margin-bottom: 2px;
        }

        /* Recuadro SIN EVIDENCIA */
        .no-evidence-box {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;        
            padding: 25px;              
            text-align: center;
            color: #94a3b8;             
            font-style: italic;
            background-color: #f8fafc;  
            margin: 20px 0;
            font-size: 11px;
        }
        
    </style>
</head>
<body>
    {{-- BLOQUE DE CONFIGURACIÓN GLOBAL --}}
    @php 
        $n = 1; // Contador de secciones

        // --------------------------------------------------------
        // PREPARAMOS LOS DATOS DEL PROFESIONAL UNA SOLA VEZ
        // --------------------------------------------------------
        
        // A. Obtener datos crudos (NUEVA ESTRUCTURA)
        $rawTipoDoc = $modulo->contenido['datos_del_profesional']['tipo_doc'] ?? '---';
        $rawNumDoc  = $modulo->contenido['datos_del_profesional']['doc'] ?? '---';
        
        $docFinal = $rawNumDoc; 
               
        // C. Preparar Nombre Completo
        $pNom = $modulo->contenido['datos_del_profesional']['nombres'] ?? '';
        $pPat = $modulo->contenido['datos_del_profesional']['apellido_paterno'] ?? '';
        $pMat = $modulo->contenido['datos_del_profesional']['apellido_materno'] ?? '';
        $profNombreCompleto = trim($pPat . ' ' . $pMat . ' ' . $pNom);
        
        if(empty($profNombreCompleto)) {
            $profNombreCompleto = '---';
        }
    @endphp

    <div class="header">
        <h1>Módulo 01: Gestión Administrativa</h1>
        <div class="header-meta">
            ACTA N° {{ str_pad($monitoreo->numero_acta, 3, '0', STR_PAD_LEFT) }} | 
            ESTABLECIMIENTO: {{ $monitoreo->establecimiento->codigo }} - {{ strtoupper($monitoreo->establecimiento->nombre) }} | 
            FECHA: 
            @php
                // 1. Buscamos la fecha específica del módulo (NUEVA ESTRUCTURA)
                $fechaRaw = $modulo->contenido['detalle_del_consultorio']['fecha_monitoreo'] ?? null;
                
                // 2. Si existe, la formateamos
                if ($fechaRaw) {
                    echo \Carbon\Carbon::parse($fechaRaw)->format('d/m/Y');
                } else {
                    echo \Carbon\Carbon::parse($monitoreo->fecha)->format('d/m/Y');
                }
            @endphp
        </div>
    </div>

    <div class="section-title">{{ $n++ }}. DETALLES DEL CONSULTORIO</div>
    <table>
        <tr>
            <td class="bg-label">Turno</td>
            <td class="uppercase">{{ $modulo->contenido['detalle_del_consultorio']['turno'] ?? '---' }}</td>
        </tr>
    </table>

    <div class="section-title">{{ $n++ }}. Datos del profesional</div>
    <table>
        <tr>
            <td class="bg-label">Apellidos y Nombres</td>
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
            <td>{{ $modulo->contenido['datos_del_profesional']['email'] ?? '---' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Celular</td>
            <td>{{ $modulo->contenido['datos_del_profesional']['telefono'] ?? '---' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Profesion</td>
            <td class="uppercase">{{ $modulo->contenido['datos_del_profesional']['cargo'] ?? '---' }}</td>
        </tr>
    </table>
    
    <div class="section-title">{{ $n++ }}. Documentación Administrativa</div>
    <table>
        <tr>
            <td class="bg-label">¿Utiliza SIHCE?</td>
            <td class="uppercase">{{ $modulo->contenido['documentacion_administrativa']['utiliza_sihce'] ?? '---' }}</td>
        </tr>
        @if(($modulo->contenido['documentacion_administrativa']['utiliza_sihce'] ?? '') != 'NO')
            <tr>
                <td class="bg-label">¿Firmó Declaración Jurada?</td>
                <td class="uppercase">{{ $modulo->contenido['documentacion_administrativa']['firmo_dj'] ?? '---' }}</td>
            </tr>
            <tr>
                <td class="bg-label">¿Firmó Compromiso de Confidencialidad?</td>
                <td class="uppercase">{{ $modulo->contenido['documentacion_administrativa']['firmo_confidencialidad'] ?? '---' }}</td>
            </tr>
        @endif
    </table>

    {{-- SECCIÓN 3: DNI Y FIRMA (CONDICIONAL) --}}
    @if(($modulo->contenido['datos_del_profesional']['tipo_doc'] ?? '') == 'DNI')
    <div class="section-title">{{ $n++ }}. DETALLE DE DNI Y FIRMA DIGITAL</div>
    <table>
        <tr>
            <td class="bg-label">Tipo de DNI</td>
            <td class="uppercase">{{ $modulo->contenido['detalle_de_dni_y_firma_digital']['tipo_dni'] ?? '---' }}</td>
        </tr>
        @if(($modulo->contenido['detalle_de_dni_y_firma_digital']['tipo_dni'] ?? '') != 'AZUL')
            <tr>
                <td class="bg-label">Versión DNIe</td>
                <td class="uppercase">{{ $modulo->contenido['detalle_de_dni_y_firma_digital']['version_dnie'] ?? '---' }}</td>
            </tr>
            <tr>
                <td class="bg-label">¿Firma digitalmente en SIHCE?</td>
                <td class="uppercase">{{ $modulo->contenido['detalle_de_dni_y_firma_digital']['firma_digital_sihce'] ?? '---' }}</td>
            </tr>
        @endif
        <tr>
            <td class="bg-label">Observaciones</td>
            <td class="uppercase">{{ $modulo->contenido['detalle_de_dni_y_firma_digital']['observaciones_dni'] ?? '---' }}</td>
        </tr>
    </table>
    @endif

    {{-- SECCIÓN 4: CAPACITACIÓN (CONDICIONAL SIHCE) --}}
    @if(($modulo->contenido['documentacion_administrativa']['utiliza_sihce'] ?? '') != 'NO')
    <div class="section-title">{{ $n++ }}. Detalles de Capacitación</div>
    <table>
        <tr>
            <td class="bg-label">¿Recibió Capacitación?</td>
            <td>{{ $modulo->contenido['detalles_de_capacitacion']['recibio_capacitacion'] ?? '---' }}</td>
        </tr>
        @if(($modulo->contenido['detalles_de_capacitacion']['recibio_capacitacion'] ?? '') != 'NO')
            <tr>
                <td class="bg-label">¿De parte de quién?</td>
                <td>{{ $modulo->contenido['detalles_de_capacitacion']['inst_que_lo_capacito'] ?? '---' }}</td>
            </tr>
        @endif
    </table>
    @endif

    <div class="section-title">{{ $n++ }}. Equipamiento del Consultorio</div>
    @php
        $equipos = \App\Models\EquipoComputo::where('cabecera_monitoreo_id', $monitoreo->id)
                    ->where('modulo', 'gestion_admin_esp')
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

    {{-- SECCIÓN: PROGRAMACIÓN SIHCE (CONDICIONAL) --}}
    @if(($modulo->contenido['documentacion_administrativa']['utiliza_sihce'] ?? '') != 'NO')
    <div class="section-title">{{ $n++ }}. Programación Actual SIHCE</div>
    <table>
        <tr>
            <td class="bg-label">Fecha Límite de Programación</td>
            <td class="uppercase">
                @php
                    $fechaProg = $modulo->contenido['gestion_admin_esp']['fecha_programacion'] ?? null;
                    if ($fechaProg) {
                        echo \Carbon\Carbon::parse($fechaProg . '-01')->format('m/Y');
                    } else {
                        echo '---';
                    }
                @endphp
            </td>
        </tr>
    </table>
    @endif

    {{-- SECCIÓN: SOPORTE (CONDICIONAL SIHCE) --}}
    @if(($modulo->contenido['documentacion_administrativa']['utiliza_sihce'] ?? '') != 'NO')
    <div class="section-title">{{ $n++ }}. Soporte</div>
    <table>
        <tr>
            <td class="bg-label">ANTE DIFICULTADES SE COMUNICA CON</td>
            <td class="uppercase">{{ $modulo->contenido['soporte']['inst_a_quien_comunica'] ?? '---' }}</td>
        </tr>
        <tr>
            <td class="bg-label">MEDIO QUE UTILIZA</td>
            <td>{{ $modulo->contenido['soporte']['medio_que_utiliza'] ?? '---' }}</td>
        </tr>
    </table>
    @endif

    <div class="section-title">{{ $n++ }}. Comentarios</div>
    <div style="border: 1px solid #e2e8f0; padding: 10px; min-height: 40px;" class="uppercase">
        {{ $modulo->contenido['comentarios_y_evidencias']['comentarios'] ?? 'SIN COMENTARIOS.' }}
    </div>

    {{-- EVIDENCIA FOTOGRÁFICA --}}
    <div class="section-title">{{ $n++ }}. Evidencia Fotográfica</div>

    @if(!empty($imagenesData) && is_array($imagenesData) && count($imagenesData) > 0)
        
        @if(count($imagenesData) === 1)
            {{-- Caso 1 Foto --}}
            <div class="foto-container">
                <img src="{{ $imagenesData[0] }}" class="foto" alt="Evidencia">
            </div>
        @else
            {{-- Caso Múltiples Fotos --}}
            <div style="text-align: center; padding: 10px; border: 1px solid #ffffff; background-color: #f9fafc;">
                <table style="width: 100%; border: none;">
                    <tr>
                        @foreach($imagenesData as $index => $img)
                            @if($index > 0 && $index % 2 == 0) 
                                </tr><tr> 
                            @endif
                            <td style="border: none; padding: 5px; text-align: center; width: 50%;">
                                <div style="border: 1px solid #cbd5e1; padding: 4px; background: #fff; border-radius: 10px;">
                                    <img src="{{ $img }}" style="max-width: 100%; height: 250px; object-fit: cover; border-radius: 8px;">
                                </div>
                            </td>
                        @endforeach
                        @if(count($imagenesData) % 2 != 0)
                            <td style="border: none;"></td>
                        @endif
                    </tr>
                </table>
            </div>
        @endif

    @else
        {{-- RECUADRO "SIN EVIDENCIA" --}}
        <div class="no-evidence-box">
            No se adjuntó evidencia fotográfica.
        </div>
    @endif

    {{-- FIRMAS --}}
    <div class="firma-section">
        <div class="section-title">{{ $n++ }}. Firma</div>
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