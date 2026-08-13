<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CabeceraMonitoreo;
use App\Models\MonitoreoModulos;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MedicinaFamiliarESPpdfController extends Controller
{
    /**
     * Genera el PDF del módulo "Medicina Familiar Especializada".
     */
    public function generar($id)
    {
        // 1. Obtener datos de la cabecera (Establecimiento, Equipo, Usuario)
        $monitoreo = CabeceraMonitoreo::with(['establecimiento', 'equipo', 'user'])->findOrFail($id);

        // 2. Obtener los datos guardados del módulo específico ('sm_med_familiar')
        $modulo = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
                                  ->where('modulo_nombre', 'sm_med_familiar')
                                  ->firstOrFail();
        
        // 3. Procesar imágenes (Convertir a Base64)
        $imagenesData = [];
        
        // --- CORRECCIÓN AQUÍ: BUSCAR EN ESTRUCTURA NUEVA PRIMERO ---
        // 1. Intentamos leer de la nueva ubicación
        $fotos = $modulo->contenido['comentarios_y_evidencias']['foto_evidencia'] ?? null;

        // 2. Si está vacío, intentamos leer de la ubicación antigua (compatibilidad)
        if (empty($fotos)) {
            $fotos = $modulo->contenido['foto_evidencia'] ?? [];
        }

        // Aseguramos que sea un array
        if (is_string($fotos)) $fotos = [$fotos];

        foreach ($fotos as $path) {
            if ($path) {
                $isFullUrl = str_starts_with($path, 'http');
                if ($isFullUrl) {
                    $imagenesData[] = $path;
                } elseif (Storage::disk('public')->exists($path)) {
                    // Obtenemos la ruta absoluta del archivo en el servidor
                    $rutaAbsoluta = storage_path("app/public/{$path}");
                    
                    // Validación extra: asegurarse que el archivo físico existe
                    if (file_exists($rutaAbsoluta)) {
                        // Obtenemos el tipo de archivo (jpg, png, etc.)
                        $extension = pathinfo($rutaAbsoluta, PATHINFO_EXTENSION);
                        
                        // Leemos el contenido del archivo y lo convertimos a Base64
                        $data = file_get_contents($rutaAbsoluta);
                        $base64 = 'data:image/' . $extension . ';base64,' . base64_encode($data);
                        
                        $imagenesData[] = $base64;
                    }
                }
            }
        }

        // 4. Generar PDF
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

        $pdf = Pdf::loadView('usuario.monitoreo.pdf_especializados.medicina_familiar_pdf', compact('monitoreo', 'modulo', 'imagenesData', 'usuarioLogeado', 'firma_jefe'));
        
        // Configuramos el papel
        $pdf->setPaper('a4', 'portrait');

        // -----------------------------------------------------------
        // INYECCIÓN DEL PIE DE PÁGINA
        // -----------------------------------------------------------
        
        // 1. Renderizamos el HTML en memoria (Vital para saber el total de páginas)
        $pdf->render();

        // 2. Obtenemos el objeto Canvas para "dibujar" sobre el PDF ya generado
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->get_canvas();
        
        // 3. Variables de diseño
        $h = $canvas->get_height();
        $w = $canvas->get_width();
        
        // Fuente y Color (Gris Slate-400 aprox)
        $fontMetrics = $dompdf->getFontMetrics();
        $font = $fontMetrics->get_font("Helvetica", "normal");
        $size = 8;
        $color = array(0.58, 0.64, 0.72); 

        // A. TEXTO DEL SISTEMA (IZQUIERDA)
        $textLeft = "SISTEMA DE ACTAS"; 
        $canvas->page_text(42, $h - 40, $textLeft, $font, $size, $color);

        // B. PAGINACIÓN (DERECHA)
        $textPag = "PAG. {PAGE_NUM} / {PAGE_COUNT}";
        $widthPag = $fontMetrics->getTextWidth("PAG. 00 / 00", $font, $size); 
        $canvas->page_text($w - 42 - $widthPag, $h - 40, $textPag, $font, $size, $color);

        // C. Dibujar línea divisoria superior
        $canvas->page_script('
            $pdf->line(42, $pdf->get_height() - 50, $pdf->get_width() - 42, $pdf->get_height() - 50, array(0.88, 0.91, 0.94), 1);
        ');
        // -----------------------------------------------------------
       
        return $pdf->stream("04.3_Medicina_Familiar_ESP_Acta_{$monitoreo->numero_acta}.pdf");
    }
}
