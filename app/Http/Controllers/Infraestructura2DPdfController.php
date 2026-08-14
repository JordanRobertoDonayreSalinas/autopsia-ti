<?php

namespace App\Http\Controllers;

use App\Models\CabeceraMonitoreo;
use App\Models\MonitoreoModulos;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class Infraestructura2DPdfController extends Controller
{
    /**
     * Genera el reporte PDF del Módulo Infraestructura 2D (Croquis).
     */
    public function generar($id)
    {
        // 1. Cargar el acta con el establecimiento
        $acta = CabeceraMonitoreo::with('establecimiento')->findOrFail($id);

        // 2. Cargar el detalle guardado para el módulo infraestructura_2d.
        //    El reporte se arma con lo guardado: si el croquis todavía no se ha
        //    guardado no hay nada que imprimir, y conviene decirlo con claridad
        //    en vez de dejar el 404 seco de firstOrFail().
        $modulo = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', 'infraestructura_2d')
            ->first();

        if (!$modulo) {
            abort(404, 'Todavía no se ha guardado el croquis de este establecimiento. Guárdelo desde el editor y vuelva a exportar el PDF.');
        }

        // 3. Contenido del croquis (elementos y conexiones)
        $contenido = $modulo->contenido ?? [];
        $elementos = $contenido['elementos'] ?? [];
        $conexiones = $contenido['conexiones'] ?? [];

        // 4. Agrupar elementos por tipo para el reporte
        $grupos = [];
        foreach ($elementos as $el) {
            $tipo = ucfirst($el['type'] ?? 'Otro');
            $grupos[$tipo][] = $el;
        }

        // 4.1 Cuadros del reporte: los ambientes por un lado y el equipamiento por
        //     otro, indicando en qué ambiente está ubicado cada equipo.
        [$ambientes, $equipos, $sistemas, $accesos, $resumen] = $this->_armarCuadros($elementos);

        // 5. Captura del monitor autenticado
        $user = Auth::user();
        $monitor = [
            'nombre' => mb_strtoupper("{$user->apellido_paterno} {$user->apellido_materno}, {$user->name}", 'UTF-8'),
            'tipo_doc' => $user->tipo_documento ?? 'DNI',
            'documento' => $user->documento ?? $user->username ?? '________',
        ];

        // 6. Cargar la vista PDF
        // Extraer firma_jefe
        $firma_jefe = '0';
        if (isset($monitoreoModulo)) {
            $c = $monitoreoModulo->contenido ?? [];
            $firma_jefe = $c['profesional']['firma_jefe'] ?? $c['rrhh']['firma_jefe'] ?? $c['personal']['firma_jefe'] ?? $c['datos_del_profesional']['firma_jefe'] ?? $c['firma_jefe'] ?? '0';
        } else {
            $mod_data = \App\Models\MonitoreoModulos::where('cabecera_monitoreo_id', $id)->get();
            foreach($mod_data as $md) {
                $c = $md->contenido ?? [];
                $fj = $c['profesional']['firma_jefe'] ?? $c['rrhh']['firma_jefe'] ?? $c['personal']['firma_jefe'] ?? $c['datos_del_profesional']['firma_jefe'] ?? $c['firma_jefe'] ?? '0';
                if ($fj == '1') {
                    $firma_jefe = '1';
                    break;
                }
            }
        }

        $pdf = Pdf::loadView('usuario.monitoreo.pdf.infraestructura_2d_pdf', compact(
            'acta',
            'modulo',
            'contenido',
            'elementos',
            'conexiones',
            'grupos',
            'ambientes',
            'equipos',
            'sistemas',
            'accesos',
            'resumen',
            'monitor'
        , 'firma_jefe'));

        // 7. Configuración del papel
        $pdf->setPaper('a4', 'portrait');

        // 8. Renderizar para paginación
        $pdf->render();

        // 9. Dibujar pie de página en todas las páginas
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $h = $canvas->get_height();
        $w = $canvas->get_width();
        $fontMetrics = $dompdf->getFontMetrics();
        $font = $fontMetrics->getFont('Helvetica', 'normal');
        $size = 8;
        $color = [0.39, 0.45, 0.54]; // Slate-500

        $canvas->page_text(42, $h - 40, 'SISTEMA DE ACTAS · INFRAESTRUCTURA 2D', $font, $size, $color);

        $textPag = 'PAG. {PAGE_NUM} / {PAGE_COUNT}';
        $widthPag = $fontMetrics->getTextWidth('PAG. 00 / 00', $font, $size);
        $canvas->page_text($w - 42 - $widthPag, $h - 40, $textPag, $font, $size, $color);

        $canvas->page_script('
            $pdf->line(42, $pdf->get_height() - 50, $pdf->get_width() - 42, $pdf->get_height() - 50, [0.88, 0.91, 0.94], 1);
        ');

        return $pdf->stream('19_Infraestructura2D_Acta_' . $acta->numero_acta . '.pdf');
    }
    /**
     * Reparte los elementos del croquis en los cuadros del reporte:
     * ambientes por un lado y equipamiento por otro, con la ubicación de cada equipo.
     *
     * @return array{0:array,1:array,2:array,3:array,4:array}
     */
    private function _armarCuadros(array $elementos): array
    {
        $AMBIENTE_LABEL = [
            'consultorio'           => 'Consultorio físico',
            'consultorio_fisico'    => 'Consultorio físico',
            'consultorio_funcional' => 'Consultorio funcional',
            'emergencias'           => 'Emergencia',
            'quirofano'             => 'Quirófano',
            'administracion'        => 'Administrativo',
            'baño'                  => 'Servicios higiénicos',
        ];

        $EQUIPO_LABEL = [
            'pc' => 'CPU', 'laptop' => 'Laptop', 'tablet' => 'Tablet', 'monitor' => 'Monitor',
            'teclado' => 'Teclado', 'mouse' => 'Mouse', 'impresora' => 'Impresora',
            'ticketera' => 'Ticketera', 'escaner' => 'Lector DNIe', 'ups' => 'UPS / Estabilizador',
            'router' => 'Router', 'ap' => 'Access Point', 'switch' => 'Switch',
            'punto_red' => 'Punto de red', 'pozo' => 'Pozo a tierra', 'equipo' => 'Equipo',
        ];

        $VIA_LABEL = ['avenida' => 'Avenida', 'jiron' => 'Jirón', 'pasaje' => 'Pasaje'];

        // Índice de ambientes para resolver dónde está ubicado cada equipo
        $porId = [];
        foreach ($elementos as $el) {
            if (!empty($el['id'])) $porId[$el['id']] = $el;
        }

        $ambientes = [];
        $equipos   = [];
        $sistemas  = [];
        $accesos   = [];
        $resumen   = [
            'ambientes' => 0, 'equipos' => 0, 'unidades' => 0,
            'OPERATIVO' => 0, 'REGULAR' => 0, 'INOPERATIVO' => 0,
            'sin_estado' => 0, 'pisos' => [],
        ];

        foreach ($elementos as $el) {
            $tipo    = mb_strtolower($el['type'] ?? '', 'UTF-8');
            $subtipo = mb_strtolower($el['subtype'] ?? '', 'UTF-8');
            $piso    = (int)($el['piso'] ?? 1);
            $resumen['pisos'][$piso] = true;

            if ($tipo === 'ambiente' || $tipo === 'pasillo') {
                $attrs = $el['attrs'] ?? [];
                $ambientes[] = [
                    'id'       => $el['id'] ?? null,
                    'nombre'   => trim((string)($el['name'] ?? '')) ?: 'Sin nombre',
                    'tipo'     => $AMBIENTE_LABEL[$subtipo] ?? ucfirst(str_replace('_', ' ', $subtipo ?: $tipo)),
                    'piso'     => $piso,
                    'wifi'     => !empty($attrs['wifi']),
                    'luz'      => !empty($attrs['light']),
                    'red'      => (int)($attrs['red'] ?? 0),
                    'equipos'  => 0,   // se completa después
                    'unidades' => 0,
                ];
                $resumen['ambientes']++;
                continue;
            }

            if ($tipo === 'hardware') {
                $cant   = max(1, (int)($el['cantidad'] ?? 1));
                $estado = mb_strtoupper(trim((string)($el['estado'] ?? '')), 'UTF-8');

                $padre = $el['parentId'] ?? null;
                $ubicacion = 'Sin ubicación asignada';
                if ($padre && isset($porId[$padre])) {
                    $ubicacion = trim((string)($porId[$padre]['name'] ?? '')) ?: 'Ambiente sin nombre';
                }

                $equipos[] = [
                    'equipo'    => $EQUIPO_LABEL[$subtipo] ?? (trim((string)($el['name'] ?? '')) ?: 'Equipo'),
                    'detalle'   => trim((string)($el['name'] ?? '')),
                    'cantidad'  => $cant,
                    'estado'    => $estado ?: '—',
                    'ubicacion' => $ubicacion,
                    'parentId'  => $padre,
                    'piso'      => $piso,
                ];

                $resumen['equipos']++;
                $resumen['unidades'] += $cant;
                if (isset($resumen[$estado])) $resumen[$estado] += $cant;
                else $resumen['sin_estado'] += $cant;
                continue;
            }

            if ($tipo === 'sistema') {
                $padre = $el['parentId'] ?? null;
                $sistemas[] = [
                    'sistema'   => mb_strtoupper(trim((string)($el['name'] ?? $subtipo)), 'UTF-8'),
                    'ubicacion' => ($padre && isset($porId[$padre]))
                        ? (trim((string)($porId[$padre]['name'] ?? '')) ?: 'Ambiente sin nombre')
                        : 'Sin ubicación asignada',
                    'piso'      => $piso,
                ];
                continue;
            }

            if ($tipo === 'puerta' || $tipo === 'calle') {
                $accesos[] = [
                    'elemento' => $tipo === 'puerta'
                        ? ($subtipo === 'externa' ? 'Portón / acceso principal' : 'Puerta interior')
                        : ($VIA_LABEL[$subtipo] ?? 'Vía'),
                    'nombre'   => trim((string)($el['name'] ?? '')) ?: '—',
                    'piso'     => $piso,
                ];
            }
        }

        // Cuántos equipos hay en cada ambiente
        foreach ($ambientes as &$amb) {
            foreach ($equipos as $eq) {
                if ($eq['parentId'] && $amb['id'] && $eq['parentId'] === $amb['id']) {
                    $amb['equipos']++;
                    $amb['unidades'] += $eq['cantidad'];
                }
            }
        }
        unset($amb);

        // Orden de lectura: por piso y, dentro del piso, alfabético
        usort($ambientes, fn($a, $b) => $a['piso'] <=> $b['piso'] ?: strcmp($a['nombre'], $b['nombre']));
        usort($equipos, fn($a, $b) => $a['piso'] <=> $b['piso']
            ?: strcmp($a['ubicacion'], $b['ubicacion'])
            ?: strcmp($a['equipo'], $b['equipo']));
        usort($sistemas, fn($a, $b) => $a['piso'] <=> $b['piso'] ?: strcmp($a['ubicacion'], $b['ubicacion']));
        usort($accesos, fn($a, $b) => $a['piso'] <=> $b['piso'] ?: strcmp($a['elemento'], $b['elemento']));

        $resumen['pisos'] = count($resumen['pisos']);

        return [$ambientes, $equipos, $sistemas, $accesos, $resumen];
    }
}
