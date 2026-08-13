    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8">
        <title>Módulo 02: Citas - Acta {{ $acta->numero_acta }}</title>
        <style>
            /* --- CONFIGURACIÓN DE PÁGINA --- */
            @page {
                /* Márgenes: Arriba, Derecha, Abajo, Izquierda */
                margin: 1.2cm 1.5cm 2cm 1.5cm;
            }

            /* --- ESTILOS GLOBALES --- */
            body {
                font-family: 'Helvetica', sans-serif;
                font-size: 10px;
                color: #1e293b;
                line-height: 1.4;
                text-transform: uppercase;
            }

            /* --- CABECERA --- */
            .header {
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 2px solid #4f46e5;
                padding-bottom: 10px;
            }

            .header h1 {
                margin: 0;
                font-size: 15px;
                color: #4f46e5;
            }

            .header-sub {
                font-weight: bold;
                color: #64748b;
                font-size: 10px;
                margin-top: 5px;
            }

            /* --- SECCIONES --- */
            .section-title {
                background-color: #f1f5f9;
                padding: 6px 10px;
                font-weight: bold;
                border-left: 4px solid #4f46e5;
                margin-top: 15px;
                margin-bottom: 5px;
                font-size: 10px;
            }

            /* --- TABLAS --- */
            table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
                margin-bottom: 5px;
            }

            th,
            td {
                border: 1px solid #e2e8f0;
                padding: 6px 8px;
                text-align: left;
                vertical-align: middle;
                word-wrap: break-word;
            }

            th {
                background-color: #f8fafc;
                color: #475569;
                font-size: 8.5px;
            }

            .bg-label {
                background-color: #f8fafc;
                font-weight: bold;
                width: 30%;
                color: #475569;
            }

            .text-center {
                text-align: center;
            }

            .text-left {
                text-align: left;
            }

            /* --- ESTADOS --- */
            .status-ok {
                color: #16a34a;
                font-weight: bold;
            }

            .status-warn {
                color: #d97706;
                font-weight: bold;
            }

            .status-err {
                color: #dc2626;
                font-weight: bold;
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

            /* --- FIRMA --- */
            .firma-section {
                margin-top: 30px;
                page-break-inside: avoid;
            }

            .firma-container {
                width: 50%;
                margin: 0 auto;
                text-align: center;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 15px;
            }

            .firma-linea {
                border-bottom: 1px solid #000;
                height: 80px;
                margin-bottom: 10px;
                width: 80%;
                margin-left: 10%;
            }

            .firma-nombre {
                font-weight: bold;
                font-size: 11px;
            }

            .firma-cargo {
                font-size: 9px;
                color: #64748b;
                margin-top: 4px;
            }

            /* --- PIE DE PÁGINA (ESTRUCTURA VISUAL) --- */
            .footer-frame {
                position: fixed;
                bottom: -1.2cm;
                left: 0;
                right: 0;
                height: 1cm;
                border-top: 1px solid #94a3b8;

                /* --- AJUSTES DE ALINEACIÓN --- */
                padding-top: 5px;
                /* Reducimos un poco el espacio superior */
                font-size: 8pt;
                /* BAJAMOS A 8 PUNTOS para que sea más fino */
                font-family: 'Helvetica', sans-serif;
                color: #64748b;
                font-weight: bold;
            }

            /* El texto "SISTEMA DE ACTAS" se alinea a la izquierda por defecto */
            
            /* --- ESTILO UNIFICADO PARA FOTOS (PREMIUM) --- */
            .photo-img, .foto, .preview-image, .photo-box img {
                width: 100%;
                height: 250px;
                object-fit: cover;
                border-radius: 10px;
                border: 1px solid #e2e8f0;
            }
        </style>
    </head>

    <body>
        @php $n = 1; @endphp
        <div class="footer-frame">
            SISTEMA DE ACTAS
        </div>

        <div class="header">
            <h1>MÓDULO 02: CITAS</h1>
            <div class="header-sub">
                ACTA N° {{ str_pad($acta->numero_acta, 3, '0', STR_PAD_LEFT) }} |
                ESTABLECIMIENTO: {{ $acta->establecimiento->codigo ?? '-' }} -
                {{ $acta->establecimiento->nombre ?? 'NO ESPECIFICADO' }} |
                FECHA: {{ \Carbon\Carbon::parse($acta->fecha ?? $registro->fecha_registro)->format('d/m/Y') }}
            </div>
        </div>

        <div class="section-title">{{ $n++ }}. DETALLES DE VENTANILLA</div>
        <table>
            <tr>
                <td class="bg-label">N° VENTANILLAS</td>
                <td>{{ $registro->nro_ventanillas ?? '0' }}</td>
            </tr>
        </table>

        <div class="section-title">{{ $n++ }}. DATOS DEL PROFESIONAL</div>
        <table>
            <tr>
                <td class="bg-label">APELLIDOS Y NOMBRES</td>
                <td class="uppercase">{{ $registro->personal_nombre ?? '-' }}</td>
            </tr>
            <tr>
                <td class="bg-label">TIPO DOC</td>
                {{-- Lee directo del registro guardado, no del modelo externo --}}
                <td>{{ $profesional->tipo_doc ?? '-' }}</td>
            </tr>
            <tr>
                <td class="bg-label">DOCUMENTO</td>
                <td>{{ $registro->personal_dni ?? '-' }}</td>
            </tr>
            <tr>
                <td class="bg-label">CARGO</td>
                <td class="uppercase">{{ $registro->personal_cargo ?? '-' }}</td>
            </tr>
            <tr>
                <td class="bg-label">CORREO ELECTRÓNICO</td>
                {{-- CAMBIO CLAVE AQUÍ: Usar $registro->personal_correo --}}
                <td>{{ $registro->personal_correo ?? '-' }}</td>
            </tr>
            <tr>
                <td class="bg-label">CELULAR</td>
                {{-- CAMBIO CLAVE AQUÍ: Usar $registro->personal_celular --}}
                <td>{{ $registro->personal_celular ?? '-' }}</td>
            </tr>
            <tr>
                <td class="bg-label">ROLES ASIGNADOS</td>
                <td>
                    @php
                        $roles = $registro->personal_roles;
                        // Limpiamos si viene como string sucio "[]" o es null
                        if ($roles === '[]' || empty($roles)) {
                            echo 'NINGUNO';
                        } elseif (is_array($roles)) {
                            // Si es un array real (gracias a $casts)
                            echo empty($roles) ? 'NINGUNO' : implode(', ', $roles);
                        } else {
                            // Si es string con contenido '["ADMIN"]'
                            $limpio = str_replace(['[', ']', '"'], '', $roles);
                            echo empty(trim($limpio)) ? 'NINGUNO' : $limpio;
                        }
                    @endphp
                </td>
            </tr>
            <tr>
                <td class="bg-label">TURNO</td>
                <td>{{ $registro->personal_turno ?? '-' }}</td>
            </tr>
            <tr>
                <td class="bg-label">¿UTILIZA SIHCE?</td>
                {{-- Mostramos el valor, por defecto NO si está vacío --}}
                <td>{{ $registro->utiliza_sihce ?? 'NO' }}</td>
            </tr>

            {{-- CONDICIÓN: Solo mostrar las siguientes filas si utiliza_sihce es 'SI' --}}
            @if (($registro->utiliza_sihce ?? '') == 'SI')
                <tr>
                    <td class="bg-label">¿FIRMÓ DECLARACIÓN JURADA?</td>
                    <td>{{ $registro->firma_dj ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="bg-label">¿FIRMÓ COMPROMISO DE CONFIDENCIALIDAD?</td>
                    <td>{{ $registro->firma_confidencialidad ?? '-' }}</td>
                </tr>
            @endif
        </table>

        @if (($profesional->tipo_doc ?? '') != 'CE')
            <div class="section-title">{{ $n++ }}. DETALLE DE DNI Y FIRMA DIGITAL</div>
            <table>
                <tr>
                    <td class="bg-label">TIPO DE DNI</td>
                    <td>{{ $registro->tipo_dni_fisico ?? '-' }}</td>
                </tr>
                {{-- CONDICIÓN: Ocultar si es DNI AZUL (Mostrar solo si es diferente a AZUL) --}}
                @if (($registro->tipo_dni_fisico ?? '') != 'AZUL')
                    <tr>
                        <td class="bg-label">VERSIÓN DNIe:</td>
                        <td>{{ $registro->dnie_version ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="bg-label">¿FIRMA DIGITALMENTE EN SIHCE?</td>
                        <td>{{ $registro->firma_sihce ?? '-' }}</td>
                    </tr>
                @endif
            </table>
        @endif

        <div class="section-title">{{ $n++ }}. DETALLES DE CAPACITACIÓN</div>
        <table>
            <tr>
                <td class="bg-label">¿RECIBIÓ CAPACITACIÓN?</td>
                <td>{{ $registro->capacitacion_recibida ?? '-' }}</td>
            </tr>
            @if (($registro->capacitacion_recibida ?? '') != 'NO')
                <tr>
                    <td>¿DE PARTE DE QUIÉN?</td>
                    <td>
                        @php
                            $entes = $registro->capacitacion_entes;
                            echo is_array($entes) ? $entes[0] ?? '-' : $entes ?? '-';
                        @endphp
                    </td>
                </tr>
            @endif
        </table>

        <div class="section-title">{{ $n++ }}. MATERIALES</div>
        <table>
            <tr>
                <td class="bg-label">AL INICIAR SUS LABORES CUENTA CON:</td>
                <td>{{ !empty($registro->insumos_disponibles) ? implode(', ', $registro->insumos_disponibles) : 'NINGUNO' }}
                </td>
            </tr>
        </table>

        <div class="section-title">{{ $n++ }}. EQUIPAMIENTO</div>
        <table>
            <thead>
                <tr>
                    <th width="25%">DESCRIPCIÓN</th>
                    <th width="10%">CANTIDAD</th>
                    <th width="15%">ESTADO</th>
                    <th width="15%">PROPIEDAD</th>
                    <th width="15%">N.SERIE/C.PAT</th>
                    <th width="20%">OBSERVACIÓN</th>
                </tr>
            </thead>
            <tbody>
            @php 
                $equiposDbFallback = \App\Models\EquipoComputo::where('cabecera_monitoreo_id', $acta->id)->where('modulo', 'citas')->get();
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
                        <td>{{ 1 }}</td>
                        <td class="text-left">
                            @php
                                $est = strtoupper($eq['estado'] ?? '');
                                $colores = [
                                    'OPERATIVO' => 'status-ok',
                                    'BUENO' => 'status-ok',
                                    'REGULAR' => 'status-warn',
                                ];
                                $clase = $colores[$est] ?? 'status-err';
                            @endphp
                            <span class="{{ $clase }}">{{ $est ?: '-' }}</span>
                        </td>
                        <td>{{ $eq['propiedad'] ?? '-' }}</td>
                        <td>{{ $eq['serie'] ?? 'S/N' }}</td>
                        <td>{{ $eq['observaciones'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">NO SE REGISTRARON EQUIPOS.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table>
            <tr>
                <td class="bg-label">OBSERVACIONES ADICIONALES:</td>
                <td>{{ $registro->equipos_observaciones ?? '-' }}</td>
            </tr>
        </table>

        {{-- SECCIÓN: CONECTIVIDAD --}}
        <div class="section-title">{{ $n++ }}. CONECTIVIDAD</div>
        @php
            $tipoConectividad = $registro->tipo_conectividad ?? null;
            $wifiFuente = $registro->wifi_fuente ?? null;
            $operadorServicio = $registro->operador_servicio ?? null;
        @endphp
        <table>
            <tr>
                <td class="bg-label">TIPO DE CONECTIVIDAD</td>
                <td>{{ $tipoConectividad ?? '---' }}</td>
            </tr>
            @if ($tipoConectividad == 'WIFI')
                <tr>
                    <td class="bg-label">FUENTE DE WIFI</td>
                    <td>{{ $wifiFuente ?? '---' }}</td>
                </tr>
            @endif
            @if ($tipoConectividad != 'SIN CONECTIVIDAD')
                <tr>
                    <td class="bg-label">OPERADOR DE SERVICIO</td>
                    <td>{{ $operadorServicio ?? '---' }}</td>
                </tr>
                @php
                    $v_desc = $registro->velocidad_descarga ?? $registro->velocidad_internet_cantidad ?? null;
                    $v_sub  = $registro->velocidad_subida ?? null;
                    $v_desc_uni = $registro->velocidad_descarga_unidad ?? $registro->velocidad_internet_unidad ?? 'Mbps';
            $v_sub_uni = $registro->velocidad_subida_unidad ?? $registro->velocidad_internet_unidad ?? 'Mbps';
                @endphp
                @if ($v_desc || $v_sub)
                <tr>
                    <td class="bg-label">VELOCIDAD DESCARGA</td>
                    <td>{{ $v_desc ? $v_desc . ' ' . $v_desc_uni : '---' }}</td>
                </tr>
                <tr>
                    <td class="bg-label">VELOCIDAD SUBIDA</td>
                    <td>{{ $v_sub ? $v_sub . ' ' . $v_sub_uni : '---' }}</td>
                </tr>
                
                @endif
            @endif
        </table>

        @if (($registro->utiliza_sihce ?? '') == 'SI')

            <div class="section-title">{{ $n++ }}. GESTIÓN DE CITAS Y CALIDAD DE ATENCIÓN</div>

            <table style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th>SERVICIO</th>
                        <th width="20%" class="text-center">CITAS OTORGADAS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($registro->produccion_listado ?? [] as $prod)
                        <tr>
                            <td>{{ $prod['nombre'] ?? '-' }}</td>
                            <td class="text-center"><strong>{{ $prod['cantidad'] ?? 0 }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 5px; font-weight: bold;">CON EL SISTEMA SIHCE:</div>
            <table>
                <tr>
                    <td class="bg-label">¿DISMINUYE EL TIEMPO DE ESPERA DE ATENCIÓN?</td>
                    <td>{{ $registro->calidad_tiempo_espera ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="bg-label">¿EL PACIENTE SE ENCUENTRA SATISFECHO?</td>
                    <td>{{ $registro->calidad_paciente_satisfecho ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="bg-label">¿SE UTILIZAN LOS REPORTES DEL SISTEMA?</td>
                    <td>{{ $registro->calidad_usa_reportes ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="bg-label">¿CON QUIÉN LOS SOCIALIZA?</td>
                    <td>{{ $registro->calidad_socializa_con ?? '-' }}</td>
                </tr>
            </table>

            <div class="section-title">{{ $n++ }}. SOPORTE</div>
            <table>
                <tr>
                    <td class="bg-label">ANTE DIFICULTADES SE COMUNICA CON</td>
                    <td>{{ $registro->dificultad_comunica_a ?? '0' }}</td>
                </tr>
                <tr>
                    <td class="bg-label">MEDIO QUE UTILIZA</td>
                    <td>{{ $registro->dificultad_medio_uso ?? '0' }}</td>
                </tr>
            </table>

        @endif

        <div class="section-title">{{ $n++ }}. EVIDENCIA FOTOGRÁFICA</div>

        @php
            $fotos = $registro->fotos_evidencia ?? [];
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

        <div class="firma-section">
            <div class="section-title">{{ $n++ }}. FIRMA</div>
            <br>
            <div class="firma-container">
                <div class="firma-linea">
                    @if ($registro->firma_grafica)
                        <img src="{{ $registro->firma_grafica }}" style="height: 70px; margin-top: -40px;">
                    @endif
                </div>
                <div class="firma-nombre">{{ $registro->personal_nombre ?? '___________________' }}</div>
                <div class="firma-cargo">{{ $profesional->tipo_doc ?? '________' }}:
                    {{ $registro->personal_dni ?? '________' }}</div>
                <div class="firma-cargo">FIRMA DEL PROFESIONAL ENTREVISTADO</div>
            </div>
        </div>

    </body>

    </html>
