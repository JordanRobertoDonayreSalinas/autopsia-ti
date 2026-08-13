<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CabeceraMonitoreo;
use App\Models\MonitoreoModulos;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TomaDeMuestraPdfController extends Controller
{
    public function generar($id)
    {
        $acta = CabeceraMonitoreo::with(['establecimiento', 'equipo', 'user'])->findOrFail($id);

        $registro = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
                                  ->where('modulo_nombre', 'toma_muestra')
                                  ->firstOrFail();
        
        $contenido = is_string($registro->contenido) ? json_decode((string)$registro->contenido, true) : $registro->contenido;


        // ----------------------------------------------------
        // MAPEO DE DATOS (Estructura para la vista)
        // ----------------------------------------------------
        $datos = [];
        $datos['fecha']                 = $contenido['detalle_del_consultorio']['fecha_monitoreo'] ?? date('Y-m-d');
        $datos['turno']                 = $contenido['detalle_del_consultorio']['turno'] ?? '';
        $datos['num_ambientes']         = $contenido['detalle_del_consultorio']['num_consultorios'] ?? '';
        $datos['denominacion_ambiente'] = $contenido['detalle_del_consultorio']['denominacion'] ?? '';

        $datos['rrhh'] = $contenido['datos_del_profesional'] ?? [];
        // Documentación
        $datos['rrhh']['cuenta_sihce']           = $contenido['documentacion_administrativa']['utiliza_sihce'] ?? '';
        $datos['rrhh']['firmo_dj']               = $contenido['documentacion_administrativa']['firmo_dj'] ?? '';
        $datos['rrhh']['firmo_confidencialidad'] = $contenido['documentacion_administrativa']['firmo_confidencialidad'] ?? '';

        // DNI
        $datos['tipo_dni_fisico']  = $contenido['detalle_de_dni_y_firma_digital']['tipo_dni'] ?? '';
        $datos['dnie_version']     = $contenido['detalle_de_dni_y_firma_digital']['version_dnie'] ?? '';
        $datos['dnie_firma_sihce'] = $contenido['detalle_de_dni_y_firma_digital']['firma_digital_sihce'] ?? '';
        $datos['dni_observacion']  = $contenido['detalle_de_dni_y_firma_digital']['observaciones_dni'] ?? '';

        // Capacitación
        $datos['capacitacion'] = [
            'recibieron_cap'  => $contenido['detalles_de_capacitacion']['recibio_capacitacion'] ?? '',
            'institucion_cap' => $contenido['detalles_de_capacitacion']['inst_que_lo_capacito'] ?? ''
        ];

        // Soporte
        $datos['dificultades'] = [
            'comunica' => $contenido['soporte']['inst_a_quien_comunica'] ?? '',
            'medio'    => $contenido['soporte']['medio_que_utiliza'] ?? ''
        ];

        // Equipos
        $datos['equipos'] = $contenido['equipos_de_computo'] ?? [];

        // Comentarios
        $datos['comentario_esp'] = $contenido['comentarios_y_evidencias']['comentarios'] ?? '';

        // --- IMAGEN BASE64 ---
        $fotoPath = null;
        if (!empty($contenido['comentarios_y_evidencias']['foto_evidencia'])) {
            $f = $contenido['comentarios_y_evidencias']['foto_evidencia'];
            $fotoPath = is_array($f) ? ($f[0] ?? null) : $f;
        } elseif (!empty($contenido['foto_evidencia'])) {
            $f = $contenido['foto_evidencia'];
            $fotoPath = is_array($f) ? ($f[0] ?? null) : $f;
        }

        $fotoBase64 = null;
        if ($fotoPath) {
            if (str_starts_with($fotoPath, 'http')) {
                $fotoBase64 = $fotoPath;
            } elseif (Storage::disk('public')->exists($fotoPath)) {
                try {
                    $rutaAbsoluta = storage_path("app/public/{$fotoPath}");
                    $extension = pathinfo($rutaAbsoluta, PATHINFO_EXTENSION);
                    $dataImg = file_get_contents($rutaAbsoluta);
                    if ($dataImg !== false) {
                        $fotoBase64 = 'data:image/' . $extension . ';base64,' . base64_encode($dataImg);
                    }
                } catch (\Exception $e) { }
            }
        }
        $datos['foto_path_pdf'] = $fotoBase64;

        // GENERAR PDF
        $usuarioLogeado = Auth::user();
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

        $pdf = Pdf::loadView('usuario.monitoreo.pdf_especializados.toma_de_muestra_pdf', compact('acta', 'datos', 'usuarioLogeado', 'firma_jefe'));
        
        $pdf->setPaper('a4', 'portrait');

        // INYECTAR FOOTER
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->get_canvas();
        $h = $canvas->get_height();
        $w = $canvas->get_width();
        
        $fontMetrics = $dompdf->getFontMetrics();
        $font = $fontMetrics->get_font("Helvetica", "normal");
        $size = 8;
        $color = array(0.58, 0.64, 0.72); 

        $canvas->page_text(42, $h - 40, "SISTEMA DE ACTAS", $font, $size, $color);
        $textPag = "PAG. {PAGE_NUM} / {PAGE_COUNT}";
        $widthPag = $fontMetrics->getTextWidth("PAG. 00 / 00", $font, $size); 
        $canvas->page_text($w - 42 - $widthPag, $h - 40, $textPag, $font, $size, $color);

        $canvas->page_script('
            $pdf->line(42, $pdf->get_height() - 50, $pdf->get_width() - 42, $pdf->get_height() - 50, array(0.88, 0.91, 0.94), 1);
        ');
        
        return $pdf->stream("05_Toma_Muestra_ESP_Acta_{$acta->numero_acta}.pdf");
    }
}