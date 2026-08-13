<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Toma de Muestra - Acta {{ $acta->numero_acta ?? $acta->id }}</title>
    <style>
        /* MARGENES DE PÁGINA */
        @page { margin: 1cm 1.5cm 2cm 1.5cm; }
        
        /* TIPOGRAFÍA GLOBAL */
        body { 
            font-family: 'Helvetica', sans-serif; 
            font-size: 10px; 
            color: #334155; 
            line-height: 1.4; 
        }

        /* ENCABEZADO */
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #0f766e; 
            padding-bottom: 10px; 
        }
        .header h1 { 
            font-family: 'Helvetica', sans-serif !important;
            font-weight: bold !important;
            margin: 0 0 5px 0; 
            font-size: 16px; 
            text-transform: uppercase; 
            color: #0f766e !important;
        }
        .header-meta {
            font-family: 'Helvetica', sans-serif !important;
            font-weight: bold !important;
            color: #64748b !important;
            font-size: 9px; 
            text-transform: uppercase;
        }

        /* TÍTULOS DE SECCIÓN */
        .section-title { 
            background-color: #f0fdfa; 
            color: #0f766e; 
            padding: 6px 10px; 
            font-weight: bold; 
            text-transform: uppercase; 
            border-left: 4px solid #14b8a6; 
            border-radius: 4px;
            margin-top: 15px; 
            margin-bottom: 8px; 
            font-size: 10px; 
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
            padding: 6px 8px; 
            text-align: left; 
            vertical-align: middle; 
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            font-size: 9px;
        }
        tr:last-child td { border-bottom: none; }
        th:last-child, td:last-child { border-right: none; }

        th { 
            background-color: #f1f5f9; 
            color: #475569; 
            font-weight: 700;
            text-transform: uppercase; 
        }

        .bg-label { 
            background-color: #f8fafc; 
            color: #64748b;
            font-weight: 700; 
            width: 35%;  
            text-transform: uppercase;
        }

        .uppercase { text-transform: uppercase; }
        .text-center { text-align: center; }

        /* EVIDENCIA FOTOGRÁFICA */
        .foto-container { 
            margin: 15px auto; 
            padding: 5px; 
            border: 1px solid #cbd5e1; 
            background-color: #fff; 
            border-radius: 8px;
            text-align: center; 
            display: table;
            width: auto;
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

        .no-evidence-box {
            border: 2px dashed #cbd5e1;
            border-radius: 8px;        
            padding: 20px;              
            text-align: center;
            color: #94a3b8;             
            font-style: italic;
            background-color: #f8fafc;  
            margin: 15px 0;
        }

        /* FIRMAS */
        .firma-section { margin-top: 40px; page-break-inside: avoid; }
        .firma-box { 
            width: 45%; /* Delgado */
            margin: 0 auto;
            text-align: center; 
            padding: 20px; 
            border: 1px solid #cbd5e1; 
            border-radius: 8px; 
            background-color: #ffffff;
        }
        .firma-linea { 
            border-bottom: 1px solid #000; 
            margin-bottom: 5px; 
            width: 100%;
        }
        .firma-nombre { font-weight: bold; font-size: 10px; }
        .firma-label { font-size: 9px; color: #64748b; }
    </style>
</head>
<body>
    @php 
        $n = 1;
        $rrhh = $datos['rrhh'] ?? [];
        
        $pNom = $rrhh['nombres'] ?? '';
        $pPat = $rrhh['apellido_paterno'] ?? '';
        $pMat = $rrhh['apellido_materno'] ?? '';
        $profNombreCompleto = trim($pPat . ' ' . $pMat . ' ' . $pNom);
        $profNombreCompleto = empty($profNombreCompleto) ? '---' : $profNombreCompleto;

        $rawTipoDoc = $rrhh['tipo_doc'] ?? '---';
        $rawNumDoc  = $rrhh['doc'] ?? '---';
    @endphp

    <div class="header">
        <h1>Módulo 05: Toma de Muestra</h1>
        <div class="header-meta">
            ACTA N° {{ str_pad($acta->numero_acta ?? $acta->id, 3, '0', STR_PAD_LEFT) }} | 
            ESTABLECIMIENTO: {{ $acta->establecimiento->codigo }} - {{ strtoupper($acta->establecimiento->nombre) }} | 
            FECHA: {{ \Carbon\Carbon::parse($datos['fecha'] ?? date('Y-m-d'))->format('d/m/Y') }}
        </div>
    </div>

    {{-- 1. DETALLES DEL CONSULTORIO --}}
    <div class="section-title">{{ $n++ }}. DETALLES DEL CONSULTORIO</div>
    <table>
        <tr>
            <td class="bg-label">Cantidad Consultorios</td>
            <td>{{ $datos['num_ambientes'] ?? '0' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Consultorio Entrevistado</td>
            <td class="uppercase">{{ $datos['denominacion_ambiente'] ?? '---' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Turno</td>
            <td class="uppercase">{{ $datos['turno'] ?? '---' }}</td>
        </tr>
    </table>

    {{-- 2. DATOS DEL PROFESIONAL --}}
    <div class="section-title">{{ $n++ }}. DATOS DEL PROFESIONAL</div>
    <table>
        <tr>
            <td class="bg-label">Apellidos y Nombres</td>
            <td class="uppercase">{{ strtoupper($profNombreCompleto) }}</td>
        </tr>
        <tr>
            <td class="bg-label">Tipo Documento</td>
            <td>{{ $rawTipoDoc }}</td>
        </tr>
        <tr>
            <td class="bg-label">N° Documento</td>
            <td>{{ $rawNumDoc }}</td>
        </tr>
        
        <tr>
            <td class="bg-label">Correo</td>
            <td>{{ $rrhh['email'] ?? '---' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Celular</td>
            <td>{{ $rrhh['telefono'] ?? '---' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Profesion</td>
            <td class="uppercase">{{ $rrhh['cargo'] ?? '---' }}</td>
        </tr>
    </table>

    {{-- 3. DOCUMENTACIÓN ADMINISTRATIVA --}}
    <div class="section-title">{{ $n++ }}. DOCUMENTACIÓN ADMINISTRATIVA</div>
    <table>
        <tr>
            <td class="bg-label">¿Utiliza SIHCE?</td>
            <td class="uppercase">{{ $rrhh['cuenta_sihce'] ?? 'NO' }}</td>
        </tr>
        @if(($rrhh['cuenta_sihce'] ?? '') != 'NO')
            <tr>
                <td class="bg-label">¿Firmó Declaración Jurada?</td>
                <td class="uppercase">{{ $rrhh['firmo_dj'] ?? 'NO' }}</td>
            </tr>
            <tr>
                <td class="bg-label">¿Firmó Confidencialidad?</td>
                <td class="uppercase">{{ $rrhh['firmo_confidencialidad'] ?? 'NO' }}</td>
            </tr>
        @endif
    </table>
    
    {{-- 4. DNI Y FIRMA --}}
    @if(($rawTipoDoc ?? '') == 'DNI')
    <div class="section-title">{{ $n++ }}. DETALLE DE DNI Y FIRMA DIGITAL</div>
    <table>
        <tr>
            <td class="bg-label">Tipo de DNI</td>
            <td class="uppercase">{{ $datos['tipo_dni_fisico'] ?? '---' }}</td>
        </tr>
        @if(($datos['tipo_dni_fisico'] ?? '') != 'AZUL')
            <tr>
                <td class="bg-label">Versión DNIe</td>
                <td class="uppercase">{{ $datos['dnie_version'] ?? '---' }}</td>
            </tr>
            <tr>
                <td class="bg-label">¿Firma digitalmente en SIHCE?</td>
                <td class="uppercase">{{ $datos['dnie_firma_sihce'] ?? '---' }}</td>
            </tr>
        @endif
        <tr>
            <td class="bg-label">Observaciones DNI</td>
            <td class="uppercase">{{ $datos['dni_observacion'] ?? '---' }}</td>
        </tr>
    </table>
    @endif

    {{-- 5. CAPACITACIÓN --}}
    @if(($rrhh['cuenta_sihce'] ?? '') != 'NO')
    <div class="section-title">{{ $n++ }}. DETALLES DE CAPACITACIÓN</div>
    <table>
        <tr>
            <td class="bg-label">¿Recibió Capacitación?</td>
            <td>{{ $datos['capacitacion']['recibieron_cap'] ?? '---' }}</td>
        </tr>
        @if(($datos['capacitacion']['recibieron_cap'] ?? '') != 'NO')
            <tr>
                <td class="bg-label">¿De parte de quién?</td>
                <td>{{ $datos['capacitacion']['institucion_cap'] ?? '---' }}</td>
            </tr>
        @endif
    </table>
    @endif

    {{-- 6. EQUIPAMIENTO --}}
    <div class="section-title">{{ $n++ }}. EQUIPAMIENTO DE CÓMPUTO</div>
    @php
        $equipos = $datos['equipos'] ?? [];
    @endphp
    @if(count($equipos) > 0)
        <table>
            <thead>
                <tr>
                    <th width="5%" class="text-center">#</th>
                    <th width="30%">Descripción</th>
                    <th width="10%" class="text-center">Cant.</th>
                    <th width="15%">Estado</th>
                    <th width="15%">Propiedad</th>
                    <th width="15%">N° Serie / Cod. Pat</th>
                    <th width="10%">Obs.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($equipos as $index => $eq)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="uppercase">{{ $eq['descripcion'] ?? '' }}</td>
                    <td class="text-center">{{ $eq['cantidad'] ?? 1 }}</td>
                    <td>{{ $eq['estado'] ?? '' }}</td>
                    <td>{{ $eq['propio'] ?? '' }}</td>
                    <td class="uppercase">
                        {{ $eq['cod_patrimonial'] ?? ($eq['nro_serie'] ?? '---') }}
                    </td>
                    <td class="uppercase">{{ $eq['observacion'] ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="color: #94a3b8; font-style: italic; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px;">
            SIN EQUIPAMIENTO REGISTRADO
        </div>
    @endif
    
    {{-- 7. SOPORTE --}}
    @if(($rrhh['cuenta_sihce'] ?? '') != 'NO')
    <div class="section-title">{{ $n++ }}. SOPORTE</div>
    <table>
        <tr>
            <td class="bg-label">Ante dificultades comunica a:</td>
            <td class="uppercase">{{ $datos['dificultades']['comunica'] ?? '---' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Medio que utiliza:</td>
            <td>{{ $datos['dificultades']['medio'] ?? '---' }}</td>
        </tr>
    </table>
    @endif

    {{-- 8. COMENTARIOS --}}
    <div class="section-title">{{ $n++ }}. OBSERVACIONES / COMENTARIOS</div>
    <div style="border: 1px solid #e2e8f0; padding: 10px; border-radius: 6px; background-color: #fcfcfc; min-height: 40px; font-size: 10px;">
        @if(!empty($datos['comentario_esp']))
            <span class="uppercase">{{ $datos['comentario_esp'] }}</span>
        @else
            <span style="color: #94a3b8; font-style: italic;">SIN COMENTARIOS REGISTRADOS.</span>
        @endif
    </div>

    {{-- 9. EVIDENCIA FOTOGRÁFICA --}}
    <div class="section-title">{{ $n++ }}. EVIDENCIA FOTOGRÁFICA</div>

    @php
        $fotoBase64 = $datos['foto_path_pdf'] ?? null;
    @endphp

    @if(!empty($fotoBase64))
        <div class="foto-container">
            <img src="{{ $fotoBase64 }}" class="foto" alt="Evidencia Triaje">
        </div>
    @else
        <div class="no-evidence-box">
            No se adjuntó evidencia fotográfica.
        </div>
    @endif

    {{-- 10. FIRMAS --}}
    <div class="firma-section">
        <div class="section-title">{{ $n++ }}. FIRMA</div>
        
        <div class="firma-box">
            <div style="height: 70px;"></div> {{-- Altura extra --}}
            <div class="firma-linea"></div>
            <div class="firma-nombre">{{ strtoupper($profNombreCompleto) }}</div>
            <div class="firma-label">{{ $rawTipoDoc }}: {{ $rawNumDoc }}</div>
            <div class="firma-label">FIRMA DEL PROFESIONAL ENTREVISTADO</div>
        </div>
    </div>
</body>
</html>