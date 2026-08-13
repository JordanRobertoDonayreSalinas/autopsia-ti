<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Croquis - Acta {{ $acta->numero_acta }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .section-title { font-weight: bold; margin-top: 20px; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .croquis-container { text-align: center; margin-top: 20px; }
        .croquis-image { max-width: 100%; border: 1px solid #000; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Infraestructura y Croquis</h1>
        <p>Acta: {{ $acta->numero_acta }} - {{ $acta->establecimiento->nombre }}</p>
    </div>

    <div class="section-title">Datos del Establecimiento</div>
    <table>
        <tr>
            <th>Código</th><td>{{ $acta->establecimiento->codigo }}</td>
            <th>Red</th><td>{{ $acta->establecimiento->red }}</td>
        </tr>
        <tr>
            <th>Provincia</th><td>{{ $acta->establecimiento->provincia }}</td>
            <th>Distrito</th><td>{{ $acta->establecimiento->distrito }}</td>
        </tr>
    </table>

    <div class="section-title">Croquis del Establecimiento</div>
    <div class="croquis-container">
        @php
            $imagen_path = $contenido['imagen_path'] ?? null;
            $base64 = null;
            if ($imagen_path) {
                $path = storage_path('app/public/' . $imagen_path);
                if (file_exists($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }
        @endphp

        @if($base64)
            <img src="{{ $base64 }}" class="croquis-image">
        @else
            <p style="color: #999; font-style: italic;">No hay imagen de croquis disponible.</p>
        @endif
    </div>

    <div class="section-title">Inventariado de Elementos</div>
    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Subtipo</th>
                <th>Nombre</th>
                <th>Piso</th>
            </tr>
        </thead>
        <tbody>
            @foreach($elementos as $el)
                <tr>
                    <td>{{ strtoupper($el['type'] ?? '---') }}</td>
                    <td>{{ strtoupper($el['subtype'] ?? '---') }}</td>
                    <td>{{ strtoupper($el['name'] ?? '---') }}</td>
                    <td>{{ $el['piso'] ?? 1 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
