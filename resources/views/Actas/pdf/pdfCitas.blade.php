<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>AI Nº {{$acta->id}} - {{$acta->modulo}} - {{$acta->nombre_establecimiento}}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { font-size: 18px;  text-align: center;}
        .section { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 4px; font-size: 11px; }
    </style>
</head>
<body>
    <h1>ACTA DE IMPLEMENTACIÓN DEL SIHCE - REGIÓN ICA <br> SEGUN MODULOS</h1>

    <div class="section">
        <h3>Definicion</h3>
        <table>
            <tr>
                <td style="background-color: #0066cc; color: white; width: 150px;"><strong>Documento</strong></td>
                <td>{{ $acta->modulo . ' #' . $acta->id }}</td>
            </tr>
            <tr>
                <td style="background-color: #0066cc; color: white;"><strong>Fecha</strong></td>
                <td>{{ \Carbon\Carbon::parse($acta->fecha)->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Datos del Establecimiento</h3>
        <table>
            <tr>
                <td style="background-color: #0066cc; color: white; width: 150px;"><strong>Establecimiento</strong></td>
                <td>{{ $acta->codigo_establecimiento . ' - ' . $acta->nombre_establecimiento }}</td>
            </tr>
            <tr>
                <td style="background-color: #0066cc; color: white;"><strong>Provincia / Distrito</strong></td>
                <td>{{ $acta->provincia }}, {{ $acta->distrito }}</td>
            </tr>
            <tr>
                <td style="background-color: #0066cc; color: white;"><strong>Red / Microred</strong></td>
                <td>{{ $acta->red }} / {{ $acta->microred }}</td>
            </tr>
            <tr>
                <td style="background-color: #0066cc; color: white;"><strong>Responsable</strong></td>
                <td>{{ $acta->responsable }}</td>
            </tr>
        </table>
    </div>

    


    <div class="section">
        <h3>Módulo Implementado</h3>
        <table>
            <tr>
                <td style="background-color: #0066cc; color: white; width: 150px;"><strong>Módulo</strong></td>
                <td>{{ $acta->modulo }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Modalidad de Modulo Citas</h3>
        <table>
            <tr>
                <td style="background-color: #0066cc; color: white; width: 150px;"><strong>Módulo</strong></td>
                <td>{{ $acta->modalidad }}</td>
            </tr>
        </table>
    </div>


    
    <div class="section">
        <h3>Usuario(s) del Modulo</h3>
        <table>
            <thead>
                <tr>
                    <th style="background-color: #0066cc; color: white; width: 150px;">DNI</th>
                    <th style="background-color: #0066cc; color: white; width: 150px;">Apellidos y Nombres</th>
                    <th style="background-color: #0066cc; color: white; width: 150px;">Celular</th>
                    <th style="background-color: #0066cc; color: white; width: 150px;">Permisos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($acta->usuarios as $p)
                <tr>
                    <td>{{ $p->dni }}</td>
                    <td>{{ $p->apellido_paterno }} {{ $p->apellido_materno }} {{ $p->nombres }}</td>
                    <td>{{ $p->celular }}</td>
                    <td>{{ $p->permisos }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    

    <div style="margin-top: 20px;">
        <h3>Compromiso</h3>
        <table style="border-collapse: collapse; width: 100%; margin-top: 5px;">
            <tr>
                <td style="border: 1px solid #000; padding: 10px; min-height: 60px;">
                    <b>El jefe del establecimiento se compromete:</b>   <br>

                    1. ACTUALIZAR en Renipress SUSALUD las UPPS y UPS añadidas para el funcionamiento del modulo en virtud a contar con profesionales que realizan la actividad. <br>

                    2. Garantizará la continuidad de lo implementado, en caso de presentarse inconvenientes comunicará al equipo implementador de la Unidad Ejecutora. <br>

                    3. Brindar las facilidades que se requieran que garanticen la carga de la programación de turnos y consultorios con 03 meses de anticipación de acuerdo a ley. <br><br>

                    

                    <b>La Unidad Ejecutora se compromete:</b> <br>

                    1. Brindar asistencia técnica permanente a los USUARIOS del Sihce. <br>

                    2. Crear y actualizar USUARIOS según necesidad del Establecimiento.<br>
                </td>
            </tr>
        </table>
    </div>
    
    <div style="margin-top: 20px;">
        <h3>Observaciones</h3>
        <table style="border-collapse: collapse; width: 100%; margin-top: 5px;">
            <tr>
                <td style="border: 1px solid #000; padding: 10px; min-height: 60px;">
                    {!! nl2br(e($acta->observaciones)) !!}
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Firmas.</h3>
    </div>



    <div style="margin: 20px;">
        <p>Dan fe de la veracidad de los datos consignados:</p>

        <table width="100%" cellspacing="0" cellpadding="10" style="border: none;">
            <tr>
                <!-- Jefe del establecimiento -->
                <td width="50%" valign="top" style="border: none;">
                    <br><br><br><br>
                    <p><strong>Jefe del establecimiento</strong></p>
                    <p>Apellidos y Nombres: {{ $acta->responsable }}</p>
                    <p>DNI: ____________________</p>
                </td>
                 <!-- Implementador DIRESA_ICATEC -->
                <td width="50%" valign="top" style="border: none;">
                    <br><br><br><br>
                    @php
                        $imp = $acta->implementadores->first();
                    @endphp

                    @if ($imp)
                    <div>
                        <p><strong>Implementador DIRESA_ICATEC</strong></p>
                        <p>Apellidos y Nombres: {{ $imp->apellido_paterno }} {{ $imp->apellido_materno }}, {{ $imp->nombres }}</p>
                        <p>DNI: {{ $imp->dni }}</p>
                    </div>
                    @endif
                </td>
            </tr>
            <tr>
                <!-- Implementador OITE Unidad Ejecutora -->
                <td width="50%" valign="top" style="border: none;">
                    <br><br><br><br>
                    <p><strong>Implementador OITE Unidad Ejecutora</strong></p>
                    <p>Apellidos y Nombres: _________________________________</p>
                    <p>DNI: ____________________</p>
                </td>
                <!-- Responsable del Modulo del EE.SS -->
                <td width="50%" valign="top" style="border: none;">
                    <br><br><br><br>
                    <p><strong>Responsable del Modulo del EE.SS</strong></p>
                    <p>Apellidos y Nombres: _________________________________</p>
                    <p>DNI: ____________________</p>
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 80px;">
        <h4><strong>Glosario</strong></h4>
        <p>D.J. : Declaración Jurada </p>
        <p>C.C. : Compromiso de Confidencialidad </p>
    </div>



</body>
</html>