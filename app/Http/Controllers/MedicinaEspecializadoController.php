<?php

namespace App\Http\Controllers;

use App\Models\CabeceraMonitoreo;
use App\Models\MonitoreoModulos;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class MedicinaEspecializadoController extends Controller
{
    public function index($actaId)
    {
        $acta = CabeceraMonitoreo::findOrFail($actaId);

        $detalle = MonitoreoModulos::where('cabecera_monitoreo_id', $actaId)
            ->where('modulo_nombre', 'sm_medicina_general')
            ->firstOrNew();

        // TRADUCCIÓN INVERSA (Nuevo JSON -> Vista Antigua)
        // Si ya tiene el formato nuevo, lo convertimos a lo que la vista espera
        if (isset($detalle->contenido['detalle_del_consultorio'])) {
            $detalle->contenido = $this->mapToOldFormat($detalle->contenido);
        }

        if ($detalle->contenido === null) {
            $detalle->contenido = [];
        }

        $equipos = $detalle->contenido['equipos'] ?? [];

        return view('usuario.monitoreo.modulos_especializados.medicina_especializado', compact('acta', 'equipos', 'detalle'));
    }

    public function store(Request $request, $actaId)
    {
        // 1. Recoger datos base
        $contenidoRaw = $request->input('contenido', []);

        // CORRECCIÓN CRÍTICA: Buscar 'dificultades' (formato antiguo)
        // A veces llega dentro de 'contenido' y a veces fuera, aseguramos capturarlo.
        if ($request->has('dificultades')) {
            $contenidoRaw['dificultades'] = $request->input('dificultades');
        }

        // 2. Procesar Equipos
        if ($request->has('equipos')) {
            $contenidoRaw['equipos'] = array_values($request->input('equipos'));
        }

        // 3. Procesar Comentarios y Foto
        if ($request->has('comentario_esp')) {
            $contenidoRaw['comentarios']['texto'] = $request->input('comentario_esp');
        }

        if ($request->hasFile('foto_esp_file')) {
            $path = $request->file('foto_esp_file')->store('evidencias_monitoreo', 'public');
            $contenidoRaw['comentarios']['foto'] = $path;
        } else {
            // Recuperar foto anterior buscando en ambos formatos
            $anterior = MonitoreoModulos::where('cabecera_monitoreo_id', $actaId)
                ->where('modulo_nombre', 'sm_medicina_general')->first();

            if ($anterior) {
                if (isset($anterior->contenido['comentarios_y_evidencias']['foto_evidencia'][0])) {
                    $contenidoRaw['comentarios']['foto'] = $anterior->contenido['comentarios_y_evidencias']['foto_evidencia'][0];
                } elseif (isset($anterior->contenido['comentarios']['foto'])) {
                    $contenidoRaw['comentarios']['foto'] = $anterior->contenido['comentarios']['foto'];
                }
            }
        }

        // 4. TRADUCCIÓN A NUEVO FORMATO
        $contenidoNuevo = $this->mapToNewFormat($contenidoRaw);

        // 5. Guardar
        MonitoreoModulos::updateOrCreate(
            [
                'cabecera_monitoreo_id' => $actaId,
                'modulo_nombre' => 'sm_medicina_general'
            ],
            [
                'contenido' => $contenidoNuevo
            ]
        );

        return redirect()
            ->route('usuario.monitoreo.salud_mental_group.index', $actaId)
            ->with('success', 'Ficha guardada correctamente.');
    }

    // --- MAPPERS ---

    private function mapToNewFormat($old)
    {
        // LOGICA CORREGIDA: Mapear 'dificultades' (Vista) -> 'soporte' (Nuevo JSON)

        // Buscamos en 'dificultades' (lo que envía tu vista)
        $instComunica = $old['dificultades']['comunica'] ?? null;
        $medioUtiliza = $old['dificultades']['medio'] ?? null;

        // Si no está ahí, buscamos en 'soporte' por si acaso
        if (!$instComunica) {
            $instComunica = $old['soporte']['inst_a_quien_comunica'] ?? null;
        }
        if (!$medioUtiliza) {
            $medioUtiliza = $old['soporte']['medio_que_utiliza'] ?? null;
        }

        return [
            'detalle_del_consultorio' => [
                'fecha_monitoreo' => $old['fecha'] ?? null,
                'turno'           => $old['turno'] ?? null,
                'num_consultorios' => $old['num_ambientes'] ?? null,
                'denominacion'    => $old['denominacion_ambiente'] ?? null,
            ],
            'datos_del_profesional' => [
                'doc'              => $old['profesional']['doc'] ?? null,
                'tipo_doc'         => $old['profesional']['tipo_doc'] ?? null,
                'nombres'          => $old['profesional']['nombres'] ?? null,
                'apellido_paterno' => $old['profesional']['apellido_paterno'] ?? null,
                'apellido_materno' => $old['profesional']['apellido_materno'] ?? null,
                'email'            => $old['profesional']['email'] ?? null,
                'telefono'         => $old['profesional']['telefono'] ?? null,
                'cargo'            => $old['profesional']['cargo'] ?? null,
            ],
            'documentacion_administrativa' => [
                'utiliza_sihce'          => $old['doc_administrativo']['cuenta_sihce'] ?? null,
                'firmo_dj'               => $old['doc_administrativo']['firmo_dj'] ?? null,
                'firmo_confidencialidad' => $old['doc_administrativo']['firmo_confidencialidad'] ?? null,
            ],
            'detalle_de_dni_y_firma_digital' => [
                'tipo_dni'            => $old['tipo_dni_fisico'] ?? null,
                'version_dnie'        => $old['dnie_version'] ?? null,
                'firma_digital_sihce' => $old['dnie_firma_sihce'] ?? null,
                'observaciones_dni'   => $old['dni_observacion'] ?? null,
            ],
            'detalles_de_capacitacion' => [
                'recibio_capacitacion' => $old['capacitacion']['recibieron_cap'] ?? null,
                'inst_que_lo_capacito' => $old['capacitacion']['institucion_cap'] ?? null,
            ],
            // AQUÍ GUARDAMOS EN EL NUEVO FORMATO
            'soporte' => [
                'inst_a_quien_comunica' => $instComunica,
                'medio_que_utiliza'     => $medioUtiliza,
            ],
            'equipos_de_computo' => $old['equipos'] ?? [],
            'materiales' => [
                'fua'       => $old['materiales']['fua'] ?? null,
                'referencia' => $old['materiales']['referencia'] ?? null,
                'receta'    => $old['materiales']['receta'] ?? null,
                'orden_lab' => $old['materiales']['orden_lab'] ?? null,
            ],
            'comentarios_y_evidencias' => [
                'comentarios'    => $old['comentarios']['texto'] ?? null,
                'foto_evidencia' => isset($old['comentarios']['foto']) ? [$old['comentarios']['foto']] : [],
            ],
        ];
    }

    private function mapToOldFormat($new)
    {
        return [
            'fecha'                 => $new['detalle_del_consultorio']['fecha_monitoreo'] ?? null,
            'turno'                 => $new['detalle_del_consultorio']['turno'] ?? null,
            'num_ambientes'         => $new['detalle_del_consultorio']['num_consultorios'] ?? null,
            'denominacion_ambiente' => $new['detalle_del_consultorio']['denominacion'] ?? null,

            'profesional' => $new['datos_del_profesional'] ?? [],

            'doc_administrativo' => [
                'cuenta_sihce'           => $new['documentacion_administrativa']['utiliza_sihce'] ?? null,
                'firmo_dj'               => $new['documentacion_administrativa']['firmo_dj'] ?? null,
                'firmo_confidencialidad' => $new['documentacion_administrativa']['firmo_confidencialidad'] ?? null,
            ],

            'tipo_dni_fisico'   => $new['detalle_de_dni_y_firma_digital']['tipo_dni'] ?? null,
            'dnie_version'      => $new['detalle_de_dni_y_firma_digital']['version_dnie'] ?? null,
            'dnie_firma_sihce'  => $new['detalle_de_dni_y_firma_digital']['firma_digital_sihce'] ?? null,
            'dni_observacion'   => $new['detalle_de_dni_y_firma_digital']['observaciones_dni'] ?? null,

            'capacitacion' => [
                'recibieron_cap'  => $new['detalles_de_capacitacion']['recibio_capacitacion'] ?? null,
                'institucion_cap' => $new['detalles_de_capacitacion']['inst_que_lo_capacito'] ?? null,
            ],

            // AQUÍ RESTAURAMOS 'dificultades' PARA QUE LA VISTA LO RECONOZCA
            'dificultades' => [
                'comunica' => $new['soporte']['inst_a_quien_comunica'] ?? null,
                'medio'    => $new['soporte']['medio_que_utiliza'] ?? null,
            ],

            // Mantenemos 'soporte' vacío o duplicado por si acaso algún otro componente lo usa
            'soporte' => [],

            'equipos' => $new['equipos_de_computo'] ?? [],

            'materiales' => $new['materiales'] ?? [],

            'comentarios' => [
                'texto' => $new['comentarios_y_evidencias']['comentarios'] ?? null,
                'foto'  => $new['comentarios_y_evidencias']['foto_evidencia'][0] ?? null,
            ],
        ];
    }

    public function generar($id)
    {
        $acta = CabeceraMonitoreo::findOrFail($id);
        $detalle = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', 'sm_medicina_general')
            ->first();

        if (!$detalle) {
            return back()->with('error', 'Primero debe guardar la ficha.');
        }

        if (isset($detalle->contenido['detalle_del_consultorio'])) {
            $detalle->contenido = $this->mapToOldFormat($detalle->contenido);
        }

        $imagenesData = [];
        $rutaFoto = $detalle->contenido['comentarios']['foto'] ?? null;

        if ($rutaFoto) {
            $isFullUrl = str_starts_with($rutaFoto, 'http');
            if ($isFullUrl) {
                $imagenesData[] = $rutaFoto;
            } else {
                $path = public_path('storage/' . $rutaFoto);
                if (file_exists($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $imagenesData[] = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }
        }

        $pdf = Pdf::loadView('usuario.monitoreo.pdf_especializados.medicina_especializado_pdf', [
            'acta' => $acta,
            'detalle' => $detalle,
            'imagenesData' => $imagenesData
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $dom_pdf = $pdf->getDomPDF();
        $canvas = $dom_pdf->getCanvas();

        // Configuración de fuente
        $fontMetrics = $dom_pdf->getFontMetrics();
        $font = $fontMetrics->get_font("Helvetica", "bold");

        // --- CAMBIOS DE AJUSTE FINO ---
        $size = 8; // Igualamos a 8pt del CSS para que se vean idénticos
        $color = [0.392, 0.455, 0.545];

        $w = $canvas->get_width();
        $h = $canvas->get_height();

        // Coordenadas:
        $x = $w - 75;

        // AQUÍ ESTÁ EL TRUCO VERTICAL:
        // Antes tenías ($h - 43). Al poner ($h - 49), "restamos más",
        // lo que hace que el texto SUBA unos milímetros en la hoja.
        $y = $h - 49;

        $canvas->page_text($x, $y, "PAG. {PAGE_NUM} / {PAGE_COUNT}", $font, $size, $color);

        return $pdf->stream('04.1_Medicina_Acta_ESP_' . $acta->numero_acta . '.pdf');
    }
}
