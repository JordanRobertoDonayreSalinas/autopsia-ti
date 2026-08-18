<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Modulo 4.1: Medicina General - Acta {{ $acta->numero_acta }}</title>
    @include('usuario.monitoreo.pdf.partials.premium_style')
</head>

<body>
    <div class="footer-frame">
        SISTEMA DE ACTAS
    </div>
    @php
        $n = 1;

        // Helper para sacar datos del JSON de forma segura
        // Uso: $detalle->contenido['clave']
        $c = $detalle->contenido ?? [];

        // Profesional
        $prof = $c['profesional'] ?? [];
        $nombreProf = trim(
            ($prof['apellido_paterno'] ?? '') .
                ' ' .
                ($prof['apellido_materno'] ?? '') .
                ' ' .
                ($prof['nombres'] ?? ''),
        );

        // Variable helper para saber si usa SIHCE (Si es NO, oculta secciones)
        $usaSihce = $c['doc_administrativo']['cuenta_sihce'] ?? '';
    @endphp

    {{-- HEADER --}}
    <div class="header">
        <h1>Módulo 4.1: Medicina General</h1>
        <div style="font-weight: bold; color: #64748b; font-size: 10px; margin-top: 5px;">
            ACTA N° {{ str_pad($acta->numero_acta, 3, '0', STR_PAD_LEFT) }} |
            {{ strtoupper($acta->establecimiento->nombre) }} |
            FECHA: {{ \Carbon\Carbon::parse($c['fecha'] ?? $acta->fecha)->format('d/m/Y') }}
        </div>
    </div>

    {{-- 1. CONSULTORIO --}}
    <div class="section-title">{{ $n++ }}. DETALLES DEL CONSULTORIO</div>
    <table>
        <tr>
            <td class="bg-label">Nro. de Consultorios</td>
            <td>{{ $c['num_ambientes'] ?? '0' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Consultorio</td>
            <td class="uppercase">{{ $c['denominacion_ambiente'] ?? '---' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Turno</td>
            <td class="uppercase">{{ $c['turno'] ?? '---' }}</td>
        </tr>
    </table>

    {{-- 2. DATOS PROFESIONAL --}}
    <div class="section-title">{{ $n++ }}. Datos del profesional</div>
    <table>
        <tr>
            <td class="bg-label">Apellidos y Nombres</td>
            <td class="uppercase">{{ $nombreProf ?: '---' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Tipo Doc.</td>
            <td>{{ $prof['tipo_doc'] ?? 'DNI' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Documento</td>
            <td>{{ $prof['doc'] ?? '---' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Profesión</td>
            <td class="uppercase">{{ $prof['cargo'] ?? '---' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Celular</td>
            <td> {{ $prof['telefono'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Correo</td>
            <td>{{ $prof['email'] ?? '' }}</td>
        </tr>


    </table>


    {{-- 2. DATOS PROFESIONAL --}}
    <div class="section-title">{{ $n++ }}. Documentacion Administrativa</div>
    <table>
        <tr>
            <td class="bg-label">Utiliza SIHCE?</td>
            <td>{{ $c['doc_administrativo']['cuenta_sihce'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Firmo Declaracion Jurada?</td>
            <td>{{ $c['doc_administrativo']['firmo_dj'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="bg-label">Firmo Compromiso de Confidencialidad?</td>
            <td>{{ $c['doc_administrativo']['firmo_confidencialidad'] ?? '' }}</td>
        </tr>
    </table>

    {{-- 3. DNI Y FIRMA (CONDICIÓN: Solo si es DNI. Si es CE se oculta) --}}
    @if (($prof['tipo_doc'] ?? '') == 'DNI')
        <div class="section-title">{{ $n++ }}. DETALLE DE DNI Y FIRMA DIGITAL</div>
        <table>
            <tr>
                <td class="bg-label">Tipo de DNI</td>
                <td class="uppercase">{{ $c['tipo_dni_fisico'] ?? '---' }}</td>
            </tr>
            @if (($c['tipo_dni_fisico'] ?? '') == 'ELECTRONICO')
                <tr>
                    <td class="bg-label">Versión DNIe</td>
                    <td class="uppercase">{{ $c['dnie_version'] ?? '---' }}</td>
                </tr>
                <tr>
                    <td class="bg-label">¿Firma en SIHCE?</td>
                    <td class="uppercase">{{ $c['dnie_firma_sihce'] ?? '---' }}</td>
                </tr>
            @endif
            <tr>
                <td class="bg-label">Observaciones</td>
                <td class="uppercase">{{ $c['dni_observacion'] ?? '---' }}</td>
            </tr>
        </table>
    @endif

    {{-- 4. CAPACITACIÓN (CONDICIÓN: Se oculta si SIHCE es NO) --}}
    @if ($usaSihce != 'NO')
        <div class="section-title">{{ $n++ }}. Detalles de Capacitación</div>
        <table>
            <tr>
                <td class="bg-label">¿Recibió Capacitación?</td>
                <td class="uppercase">{{ $c['capacitacion']['recibieron_cap'] ?? '---' }}</td>
            </tr>
            @if (($c['capacitacion']['recibieron_cap'] ?? '') == 'SI')
                <tr>
                    <td class="bg-label">Entidad que capacitó</td>
                    <td class="uppercase">{{ $c['capacitacion']['institucion_cap'] ?? '---' }}</td>
                </tr>
            @endif
        </table>
    @endif

    {{-- 5. MATERIALES --}}
    <div class="section-title">{{ $n++ }}. Materiales</div>
    <div class="materiales-list">
        @php
            $mats = $c['materiales'] ?? [];
            $hayMateriales = false;
            // Mapeo simple de lo que guardaste
            $labels = [
                'fua' => 'FUA',
                'referencia' => 'REFERENCIA',
                'receta' => 'RECETA',
                'orden_lab' => 'ORDEN LAB.',
            ];
        @endphp

        @foreach ($labels as $key => $label)
            @if (isset($mats[$key]))
                @php $hayMateriales = true; @endphp
                <span class="materiales-item">
                    {{ $label }}: <strong>{{ $mats[$key] }}</strong>
                </span>
            @endif
        @endforeach

        @if (!$hayMateriales)
            <span style="color: #94a3b8; font-style: italic;">SIN REGISTRO DE MATERIALES</span>
        @endif
    </div>

    {{-- 6. EQUIPAMIENTO --}}
    <div class="section-title">{{ $n++ }}. Equipamiento del Consultorio</div>
    @php
        $listaEquipos = $c['equipos'] ?? [];
    @endphp

    @if (count($listaEquipos) > 0)
        <table>
            <thead>
                <tr>
                    <th width="30%">Descripción</th>
                    <th width="10%">Cant.</th>
                    <th width="15%">Estado</th>
                    <th width="15%">Propiedad</th>
                    <th width="15%">N.Serie / C.Pat</th>
                    <th width="15%">Observación</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($listaEquipos as $eq)
                    {{-- Convertimos array a objeto o array, aquí usamos array access --}}
                    @php $eq = (array)$eq; @endphp
                    <tr>
                        <td class="uppercase">{{ $eq['descripcion'] ?? '' }}</td>
                        <td class="text-center">{{ $eq['cantidad'] ?? '1' }}</td>
                        <td class="uppercase">{{ $eq['estado'] ?? '' }}</td>
                        <td class="uppercase">{{ $eq['propio'] ?? '' }}</td>
                        <td class="uppercase">{{ $eq['nro_serie'] ?? '' }}</td>
                        <td class="uppercase">{{ $eq['observacion'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="color: #94a3b8; font-style: italic; padding: 8px;">SIN EQUIPAMIENTO REGISTRADO</div>
    @endif

    {{-- 7. SOPORTE (CONDICIÓN: Se oculta si SIHCE es NO) --}}
    @if ($usaSihce != 'NO')
        <div class="section-title">{{ $n++ }}. Soporte</div>
        <table>
            <tr>
                <td class="bg-label">Ante Dificultades Comunica a</td>
                <td class="uppercase">{{ $c['dificultades']['comunica'] ?? '---' }}</td>
            </tr>
            <tr>
                <td class="bg-label">Medio que utiliza</td>
                <td class="uppercase">{{ $c['dificultades']['medio'] ?? '---' }}</td>
            </tr>
        </table>
    @endif

    {{-- 8. COMENTARIOS --}}
    <div class="section-title">{{ $n++ }}. Comentarios</div>
    <div style="border: 1px solid #e2e8f0; padding: 10px; min-height: 40px; background: #fff;" class="uppercase">
        {{ $c['comentarios']['texto'] ?? 'SIN COMENTARIOS.' }}
    </div>

    {{-- 9. EVIDENCIA FOTOGRÁFICA --}}
    <div class="section-title">{{ $n++ }}. Evidencia Fotográfica</div>

    @if (!empty($imagenesData) && count($imagenesData) > 0)
        <div class="foto-container">
            {{-- Mostramos la primera (y única) foto procesada en el controlador --}}
            <img src="{{ $imagenesData[0] }}" class="foto" alt="Evidencia">
        </div>
    @else
        <div class="no-evidence-box">
            No se adjuntó evidencia fotográfica.
        </div>
    @endif

    {{-- 10. FIRMA --}}
    <div class="firma-section">
        <div class="firma-container">
            <div class="firma-box">
                <div class="firma-linea"></div>
                <div class="firma-nombre">{{ strtoupper($nombreProf) }}</div>
                <div class="firma-label">{{ $prof['tipo_doc'] ?? 'DOC' }}: {{ $prof['doc'] ?? '---' }}</div>
                <div class="firma-label">FIRMA DEL PROFESIONAL ENTREVISTADO </div>
            </div>
        </div>
    </div>
</body>

</html>
