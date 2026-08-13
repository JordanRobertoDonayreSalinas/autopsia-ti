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

        // 2. Cargar el detalle guardado para el módulo infraestructura_2d
        $modulo = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', 'infraestructura_2d')
            ->firstOrFail();

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
}
