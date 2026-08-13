<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Monitoreo - {{ $acta->establecimiento->nombre }}</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; }
        .header { width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .title { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .section-title { background: #f1f5f9; padding: 5px 10px; font-weight: bold; border-left: 4px solid #4f46e5; margin: 15px 0 10px; text-transform: uppercase; }
        
        .table-datos { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .table-datos td { border: 1px solid #e2e8f0; padding: 6px; vertical-align: top; }
        .label { font-weight: bold; font-size: 8px; color: #64748b; text-transform: uppercase; display: block; }
        .value { font-size: 10px; font-weight: bold; }
        
        /* Tabla de Equipo y Servicios */
        .tabla-tecnica { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .tabla-tecnica th { background: #1e293b; color: white; border: 1px solid #000; padding: 5px; font-size: 8px; text-transform: uppercase; }
        .tabla-tecnica td { border: 1px solid #e2e8f0; padding: 5px; text-align: center; }
        
        .footer { margin-top: 50px; width: 100%; }
        .box-firma { text-align: center; width: 50%; }
        .linea { border-top: 1px solid #000; width: 180px; margin: 0 auto 5px; }
    </style>
</head>
<body>

    <table class="header">
        <tr>
            <td>
                <div class="title">Acta de Monitoreo y Asistencia Técnica</div>
                <div style="color: #6366f1; font-weight: bold;">Módulo 01: Programación de Consultorios y Turnos</div>
            </td>
            <td style="text-align: right;">
                <span class="label">Fecha:</span>
                <span class="value">{{ \Carbon\Carbon::parse($acta->fecha)->format('d/m/Y') }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">I. Datos del Establecimiento</div>
    <table class="table-datos">
        <tr>
            <td width="50%"><span class="label">Establecimiento:</span><span class="value">{{ $acta->establecimiento->nombre }}</span></td>
            <td width="50%"><span class="label">Ubicación:</span><span class="value">{{ $acta->establecimiento->distrito }} - {{ $acta->establecimiento->provincia }}</span></td>
        </tr>
        <tr>
            <td><span class="label">Implementador Responsable:</span><span class="value">{{ $acta->implementador }}</span></td>
            <td><span class="label">Jefe de establecimiento:</span><span class="value">{{ $acta->responsable }}</span></td>
        </tr>
    </table>

    {{-- NUEVA SECCIÓN: EQUIPO DE TRABAJO --}}
    @if($acta->equipo && $acta->equipo->count() > 0)
        <div class="section-title">II. Equipo de Trabajo / Acompañantes</div>
        <table class="tabla-tecnica">
            <thead>
                <tr>
                    <th style="text-align: left;">Apellidos y Nombres</th>
                    <th>Cargo</th>
                    <th>Institución</th>
                </tr>
            </thead>
            <tbody>
                @foreach($acta->equipo as $miembro)
                    <tr>
                        <td style="text-align: left; font-weight: bold;">{{ $miembro->nombre_completo }}</td>
                        <td>{{ $miembro->cargo }}</td>
                        <td>{{ $miembro->institucion }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- DATOS EXTRAÍDOS DE LA RELACIÓN PROGRAMACIÓN --}}
    @if($acta->programacion)
        <div class="section-title">III. Desarrollo de la Entrevista (Personal de RRHH)</div>
        <table class="table-datos">
            <tr>
                <td colspan="2"><span class="label">Responsable de RRHH:</span><span class="value">{{ $acta->programacion->rrhh_nombre }}</span></td>
                <td><span class="label">DNI:</span><span class="value">{{ $acta->programacion->rrhh_dni }}</span></td>
            </tr>
            <tr>
                <td width="33%"><span class="label">Teléfono:</span><span class="value">{{ $acta->programacion->rrhh_telefono }}</span></td>
                <td width="34%"><span class="label">Correo:</span><span class="value">{{ $acta->programacion->rrhh_correo }}</span></td>
                <td width="33%"><span class="label">Usuario ODOO:</span><span class="value">{{ $acta->programacion->odoo }}</span></td>
            </tr>
        </table>

        @if($acta->programacion->odoo == 'NO')
            <p style="margin-top: 5px;"><strong>Si la respuesta es NO ¿Quién programa los turnos y consultorios?</strong></p>
            <table class="table-datos">
                <tr>
                    <td width="60%"><span class="label">Apellidos y Nombres:</span><span class="value">{{ $acta->programacion->quien_programa_nombre }}</span></td>
                    <td width="20%"><span class="label">DNI:</span><span class="value">{{ $acta->programacion->quien_programa_dni }}</span></td>
                    <td width="20%"><span class="label">Teléfono:</span><span class="value">{{ $acta->programacion->quien_programa_telefono }}</span></td>
                </tr>
            </table>
        @endif

        <table class="table-datos" style="margin-top: 10px;">
            <tr>
                <td width="50%"><span class="label">¿Capacitación en Gestión Administrativa?</span><span class="value">{{ $acta->programacion->capacitacion }}</span></td>
                <td width="50%"><span class="label">Mes de Programación en el Sistema:</span><span class="value">{{ $acta->programacion->mes_sistema }}</span></td>
            </tr>
        </table>

        <div class="section-title">IV. Cartera de Servicios, Turnos y Cupos</div>
        @if(!empty($acta->programacion->servicios))
            <table class="tabla-tecnica">
                <thead>
                    <tr>
                        <th rowspan="2">Servicios</th>
                        <th colspan="2">Mes Actual</th>
                        <th colspan="2">Mes Próximo</th>
                        <th colspan="2">Siguiente Mes</th>
                    </tr>
                    <tr>
                        <th>T.</th><th>C.</th><th>T.</th><th>C.</th><th>T.</th><th>C.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($acta->programacion->servicios as $servicio => $cantidades)
                        <tr>
                            <td style="text-align: left; font-weight: bold;">{{ $servicio }}</td>
                            <td>{{ $cantidades['t1'] ?? '0' }}</td><td>{{ $cantidades['c1'] ?? '0' }}</td>
                            <td>{{ $cantidades['t2'] ?? '0' }}</td><td>{{ $cantidades['c2'] ?? '0' }}</td>
                            <td>{{ $cantidades['t3'] ?? '0' }}</td><td>{{ $cantidades['c3'] ?? '0' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="section-title">V. Comentarios del Usuario y/o Entrevistado</div>
        <div style="border: 1px solid #e2e8f0; padding: 10px; min-height: 50px;">
            {{ $acta->programacion->comentarios ?? 'Sin comentarios adicionales.' }}
        </div>

        <div class="footer">
            <table width="100%">
                <tr>
                    <td class="box-firma">
                        <div class="linea"></div>
                        <div class="value">{{ $acta->implementador }}</div>
                        <div class="label">Firma del Implementador</div>
                    </td>
                    <td class="box-firma">
                        <div class="linea"></div>
                        <div class="value">{{ $acta->programacion->entrevistado_nombre ?? '................................' }}</div>
                        <div class="label">Apellidos y Nombres del Entrevistado</div>
                    </td>
                </tr>
            </table>
        </div>
    @else
        <p style="text-align: center; color: red; margin-top: 50px;">NO SE HAN REGISTRADO DATOS PARA EL MÓDULO DE PROGRAMACIÓN.</p>
    @endif

</body>
</html>