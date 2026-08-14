<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Croquis - Acta {{ $acta->numero_acta }}</title>
    <style>
        @page { margin: 40px 42px 70px 42px; }

        body { font-family: sans-serif; font-size: 10px; color: #1e293b; }

        .header { text-align: center; margin-bottom: 14px; }
        .header h1 { font-size: 15px; margin: 0 0 3px 0; letter-spacing: .5px; }
        .header p { margin: 0; font-size: 10px; color: #64748b; }

        .section-title {
            font-size: 10px; font-weight: bold; text-transform: uppercase;
            letter-spacing: .8px; color: #1e293b;
            margin: 16px 0 6px 0; padding-bottom: 4px;
            border-bottom: 2px solid #4f46e5;
        }
        .section-note { font-size: 8px; color: #94a3b8; margin: 0 0 6px 0; }

        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e2e8f0; padding: 5px 6px; text-align: left; vertical-align: top; }
        th {
            background-color: #f1f5f9; font-size: 8px; text-transform: uppercase;
            letter-spacing: .5px; color: #475569;
        }
        td { font-size: 9px; }
        tr.par td { background-color: #fafcff; }
        .num { text-align: center; }

        /* Datos del establecimiento */
        .datos th { width: 15%; }
        .datos td { width: 35%; }

        /* Resumen */
        .resumen td { text-align: center; border: 1px solid #e2e8f0; }
        .resumen .cifra { font-size: 15px; font-weight: bold; color: #4f46e5; }
        .resumen .rotulo { font-size: 7px; text-transform: uppercase; color: #64748b; letter-spacing: .5px; }

        /* Estado del equipo */
        .estado { font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .op { color: #15803d; }
        .re { color: #b45309; }
        .in { color: #b91c1c; }

        .croquis-container { text-align: center; margin-top: 8px; }
        .croquis-image { max-width: 100%; border: 1px solid #cbd5e1; }
        .vacio { color: #94a3b8; font-style: italic; font-size: 9px; padding: 8px 0; }
        .subtotal td { background-color: #f8fafc; font-weight: bold; font-size: 8px; }
    </style>
</head>

<body>
    <div class="header">
        <h1>Reporte de Infraestructura y Croquis</h1>
        <p>Acta {{ $acta->numero_acta }} &middot; {{ $acta->establecimiento->nombre }}</p>
    </div>

    {{-- ─── Datos del establecimiento ─── --}}
    <div class="section-title">Datos del establecimiento</div>
    <table class="datos">
        <tr>
            <th>Código</th><td>{{ $acta->establecimiento->codigo }}</td>
            <th>Red</th><td>{{ $acta->establecimiento->red }}</td>
        </tr>
        <tr>
            <th>Provincia</th><td>{{ $acta->establecimiento->provincia }}</td>
            <th>Distrito</th><td>{{ $acta->establecimiento->distrito }}</td>
        </tr>
    </table>

    {{-- ─── Resumen ─── --}}
    <table class="resumen" style="margin-top: 10px;">
        <tr>
            <td>
                <div class="cifra">{{ $resumen['ambientes'] }}</div>
                <div class="rotulo">Ambientes</div>
            </td>
            <td>
                <div class="cifra">{{ $resumen['unidades'] }}</div>
                <div class="rotulo">Equipos</div>
            </td>
            <td>
                <div class="cifra" style="color:#15803d;">{{ $resumen['OPERATIVO'] }}</div>
                <div class="rotulo">Operativos</div>
            </td>
            <td>
                <div class="cifra" style="color:#b45309;">{{ $resumen['REGULAR'] }}</div>
                <div class="rotulo">Regulares</div>
            </td>
            <td>
                <div class="cifra" style="color:#b91c1c;">{{ $resumen['INOPERATIVO'] }}</div>
                <div class="rotulo">Inoperativos</div>
            </td>
            <td>
                <div class="cifra">{{ $resumen['pisos'] }}</div>
                <div class="rotulo">Pisos</div>
            </td>
        </tr>
    </table>

    {{-- ─── Croquis ─── --}}
    <div class="section-title">Croquis del establecimiento</div>
    <div class="croquis-container">
        @php
            $imagen_path = $contenido['imagen_path'] ?? null;
            $base64 = null;
            if ($imagen_path) {
                $path = storage_path('app/public/' . $imagen_path);
                if (file_exists($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($path));
                }
            }
        @endphp

        @if($base64)
            <img src="{{ $base64 }}" class="croquis-image">
        @else
            <p class="vacio">No hay imagen de croquis disponible.</p>
        @endif
    </div>

    {{-- ─── Cuadro 1: ambientes ─── --}}
    <div class="section-title">1. Ambientes y servicios</div>
    <p class="section-note">Espacios registrados en el croquis, con los servicios con que cuenta cada uno.</p>
    @if(count($ambientes))
        <table>
            <thead>
                <tr>
                    <th style="width:4%;" class="num">N°</th>
                    <th style="width:31%;">Ambiente</th>
                    <th style="width:20%;">Tipo</th>
                    <th style="width:7%;" class="num">Piso</th>
                    <th style="width:8%;" class="num">Wifi</th>
                    <th style="width:8%;" class="num">Energía</th>
                    <th style="width:10%;" class="num">Ptos. red</th>
                    <th style="width:12%;" class="num">Equipos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ambientes as $i => $amb)
                    <tr class="{{ $i % 2 ? 'par' : '' }}">
                        <td class="num">{{ $i + 1 }}</td>
                        <td>{{ $amb['nombre'] }}</td>
                        <td>{{ $amb['tipo'] }}</td>
                        <td class="num">{{ $amb['piso'] }}</td>
                        <td class="num">{{ $amb['wifi'] ? 'Sí' : '—' }}</td>
                        <td class="num">{{ $amb['luz'] ? 'Sí' : '—' }}</td>
                        <td class="num">{{ $amb['red'] ?: '—' }}</td>
                        <td class="num">{{ $amb['unidades'] ?: '—' }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal">
                    <td colspan="7">Total de ambientes: {{ count($ambientes) }}</td>
                    {{-- Suma de la columna: los equipos que están dentro de un ambiente --}}
                    <td class="num">{{ array_sum(array_column($ambientes, 'unidades')) }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p class="vacio">No se registraron ambientes en el croquis.</p>
    @endif

    {{-- ─── Cuadro 2: equipamiento y ubicación ─── --}}
    <div class="section-title">2. Equipamiento informático y su ubicación</div>
    <p class="section-note">Equipos registrados en el croquis, indicando el ambiente en el que se encuentran.</p>
    @if(count($equipos))
        <table>
            <thead>
                <tr>
                    <th style="width:4%;" class="num">N°</th>
                    <th style="width:23%;">Equipo</th>
                    <th style="width:8%;" class="num">Cant.</th>
                    <th style="width:16%;">Estado</th>
                    <th style="width:35%;">Ubicación (ambiente)</th>
                    <th style="width:7%;" class="num">Piso</th>
                </tr>
            </thead>
            <tbody>
                @foreach($equipos as $i => $eq)
                    @php
                        $clase = $eq['estado'] === 'OPERATIVO' ? 'op' : ($eq['estado'] === 'REGULAR' ? 're' : ($eq['estado'] === 'INOPERATIVO' ? 'in' : ''));
                    @endphp
                    <tr class="{{ $i % 2 ? 'par' : '' }}">
                        <td class="num">{{ $i + 1 }}</td>
                        <td>{{ $eq['equipo'] }}</td>
                        <td class="num">{{ $eq['cantidad'] }}</td>
                        <td><span class="estado {{ $clase }}">{{ $eq['estado'] }}</span></td>
                        <td>{{ $eq['ubicacion'] }}</td>
                        <td class="num">{{ $eq['piso'] }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal">
                    <td colspan="2">Total de equipos</td>
                    <td class="num">{{ $resumen['unidades'] }}</td>
                    <td colspan="3">
                        Operativos: {{ $resumen['OPERATIVO'] }} &middot;
                        Regulares: {{ $resumen['REGULAR'] }} &middot;
                        Inoperativos: {{ $resumen['INOPERATIVO'] }}
                    </td>
                </tr>
            </tbody>
        </table>
        @php $sinUbicar = collect($equipos)->where('ubicacion', 'Sin ubicación asignada')->sum('cantidad'); @endphp
        @if($sinUbicar)
            <p class="section-note" style="margin-top:4px;">
                {{ $sinUbicar }} equipo(s) figuran en el croquis fuera de un ambiente, por eso no tienen ubicación asignada.
            </p>
        @endif
    @else
        <p class="vacio">No se registraron equipos informáticos en el croquis.</p>
    @endif

    {{-- ─── Cuadro 3: sistemas de información ─── --}}
    @if(count($sistemas))
        <div class="section-title">3. Sistemas de información</div>
        <p class="section-note">Sistemas representados en el croquis y el ambiente donde se utilizan.</p>
        <table>
            <thead>
                <tr>
                    <th style="width:4%;" class="num">N°</th>
                    <th style="width:34%;">Sistema</th>
                    <th style="width:55%;">Ubicación (ambiente)</th>
                    <th style="width:7%;" class="num">Piso</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sistemas as $i => $sis)
                    <tr class="{{ $i % 2 ? 'par' : '' }}">
                        <td class="num">{{ $i + 1 }}</td>
                        <td>{{ $sis['sistema'] }}</td>
                        <td>{{ $sis['ubicacion'] }}</td>
                        <td class="num">{{ $sis['piso'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ─── Cuadro 4: accesos y vías ─── --}}
    @if(count($accesos))
        <div class="section-title">{{ count($sistemas) ? '4' : '3' }}. Accesos y vías</div>
        <table>
            <thead>
                <tr>
                    <th style="width:4%;" class="num">N°</th>
                    <th style="width:44%;">Elemento</th>
                    <th style="width:45%;">Denominación</th>
                    <th style="width:7%;" class="num">Piso</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accesos as $i => $ac)
                    <tr class="{{ $i % 2 ? 'par' : '' }}">
                        <td class="num">{{ $i + 1 }}</td>
                        <td>{{ $ac['elemento'] }}</td>
                        <td>{{ $ac['nombre'] }}</td>
                        <td class="num">{{ $ac['piso'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>

</html>
