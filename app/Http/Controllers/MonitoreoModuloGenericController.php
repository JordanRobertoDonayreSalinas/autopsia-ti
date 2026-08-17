<?php

namespace App\Http\Controllers;

use App\Models\CabeceraMonitoreo;
use App\Models\MonitoreoModulos;
use App\Models\EquipoComputo;
use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;

class MonitoreoModuloGenericController extends Controller
{
    /**
     * Crear un nuevo consultorio dinámico para un acta de monitoreo.
     */
    public function crearConsultorio(Request $request, $actaId)
    {
        $request->validate([
            'titulo_consultorio' => 'required|string|max:150',
        ]);

        $titulo = mb_strtoupper(trim($request->input('titulo_consultorio')));
        $acta = CabeceraMonitoreo::findOrFail($actaId);

        // Generar un slug único basado en el nombre y timestamp
        $slugBase = \Str::slug($titulo, '_');
        if (empty($slugBase)) {
            $slugBase = 'consultorio';
        }
        $slug = $slugBase . '_' . time();

        // Crear registro inicial en mon_monitoreo_modulos
        MonitoreoModulos::create([
            'cabecera_monitoreo_id' => $actaId,
            'modulo_nombre'         => $slug,
            'contenido'             => [
                'titulo_consultorio' => $titulo,
                'fecha'              => date('Y-m-d'),
                'turno'              => 'MAÑANA',
            ],
        ]);

        // Redirigir inmediatamente al formulario del nuevo consultorio
        return redirect()->route('usuario.monitoreo.consultorio.show', [$actaId, $slug])
            ->with('success', "Consultorio '{$titulo}' creado correctamente. Complete la evaluación.");
    }

    /**
     * Renombra un consultorio dinámico existente.
     */
    public function renombrarConsultorio(Request $request, $id, $slug)
    {
        $request->validate([
            'nuevo_titulo' => 'required|string|max:150',
        ]);

        $nuevoTitulo = mb_strtoupper(trim($request->input('nuevo_titulo')));

        $detalle = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', $slug)
            ->firstOrFail();

        $contenido = $detalle->contenido ?? [];
        $contenido['titulo_consultorio'] = $nuevoTitulo;

        $detalle->update(['contenido' => $contenido]);

        return redirect()->back()->with('success', "Nombre del consultorio actualizado a '{$nuevoTitulo}'.");
    }

    /**
     * Muestra el formulario de evaluación de un consultorio dinámico.
     */
    public function showConsultorio($id, $slug)
    {
        $acta = CabeceraMonitoreo::with('establecimiento')->findOrFail($id);

        $detalle = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', $slug)
            ->firstOrFail();

        $contenido = $detalle->contenido ?? [];
        $tituloConsultorio = $contenido['titulo_consultorio'] ?? 'CONSULTORIO';

        $equipos = EquipoComputo::where('cabecera_monitoreo_id', $id)
            ->where('modulo', $slug)
            ->get();

        return view('usuario.monitoreo.modulos.consultorio_dinamico', compact(
            'acta', 'detalle', 'slug', 'tituloConsultorio', 'equipos'
        ));
    }

    /**
     * Guarda la evaluación de un consultorio dinámico.
     */
    public function storeConsultorio(Request $request, $id, $slug)
    {
        try {
            DB::beginTransaction();

            $acta = CabeceraMonitoreo::findOrFail($id);
            $contenido = $request->input('contenido', []);

            $detalle = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
                ->where('modulo_nombre', $slug)
                ->firstOrFail();

            // Preservar o actualizar el título del consultorio
            if (!empty($contenido['titulo_consultorio'])) {
                $contenido['titulo_consultorio'] = mb_strtoupper(trim($contenido['titulo_consultorio']));
            } else if (isset($detalle->contenido['titulo_consultorio'])) {
                $contenido['titulo_consultorio'] = $detalle->contenido['titulo_consultorio'];
            }

            // Manejo de archivo de evidencia fotográfica si se adjunta
            if ($request->hasFile('evidencia')) {
                $path = $request->file('evidencia')->store('evidencias_monitoreo', 'public');
                $contenido['evidencia_path'] = $path;
            } else if (isset($detalle->contenido['evidencia_path'])) {
                $contenido['evidencia_path'] = $detalle->contenido['evidencia_path'];
            }

            $detalle->update(['contenido' => $contenido]);

            // Sincronizar datos del profesional entrevistado si se enviaron
            $prof = $contenido['profesional']
                 ?? $contenido['datos_del_profesional']
                 ?? $contenido['busqueda_temporal']
                 ?? null;

            if ($prof && !empty($prof['doc'])) {
                Profesional::updateOrCreate(
                    ['doc' => trim($prof['doc'])],
                    [
                        'tipo_doc'         => $prof['tipo_doc'] ?? 'DNI',
                        'nombres'          => mb_strtoupper(trim($prof['nombres'] ?? '')),
                        'apellido_paterno' => mb_strtoupper(trim($prof['apellido_paterno'] ?? '')),
                        'apellido_materno' => mb_strtoupper(trim($prof['apellido_materno'] ?? '')),
                        'cargo'            => mb_strtoupper(trim($prof['cargo'] ?? '')),
                        'email'            => trim($prof['email'] ?? ''),
                        'telefono'         => trim($prof['telefono'] ?? ''),
                    ]
                );
            }

            // Sincronizar equipos de cómputo (provenientes del componente x-tabla-equipos)
            $equiposData = $request->input('equipos', $contenido['equipos'] ?? []);
            if (is_array($equiposData) && count($equiposData) > 0) {
                EquipoComputo::where('cabecera_monitoreo_id', $id)
                    ->where('modulo', $slug)
                    ->delete();

                foreach ($equiposData as $eq) {
                    if (!empty($eq['descripcion'])) {
                        $especificaciones = $eq['especificaciones'] ?? null;
                        if (is_string($especificaciones) && !empty(trim($especificaciones))) {
                            $decoded = json_decode($especificaciones, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $especificaciones = $decoded;
                            }
                        }

                        EquipoComputo::create([
                            'cabecera_monitoreo_id' => $id,
                            'modulo'                => $slug,
                            'descripcion'           => mb_strtoupper(trim($eq['descripcion'] ?? '')),
                            'cantidad'              => (int)($eq['cantidad'] ?? 1),
                            'estado'                => mb_strtoupper(trim($eq['estado'] ?? 'OPERATIVO')),
                            'propio'                => mb_strtoupper(trim($eq['propio'] ?? 'EXCLUSIVO')),
                            'nro_serie'             => mb_strtoupper(trim($eq['nro_serie'] ?? $eq['serie'] ?? '')),
                            'observacion'           => mb_strtoupper(trim($eq['observacion'] ?? $eq['observaciones'] ?? '')),
                            'especificaciones'      => is_array($especificaciones) ? $especificaciones : null
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('usuario.monitoreo.modulos', $id)
                ->with('success', 'Evaluación del consultorio guardada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error al guardar consultorio {$slug} para el acta #{$id}: " . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al guardar el consultorio: ' . $e->getMessage());
        }
    }

    /**
     * Genera el reporte PDF para un consultorio dinámico.
     */
    public function pdfConsultorio($id, $slug)
    {
        $acta = CabeceraMonitoreo::with(['establecimiento', 'equipo'])->findOrFail($id);

        $detalle = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', $slug)
            ->firstOrFail();

        $contenido = $detalle->contenido ?? [];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('usuario.monitoreo.pdf.consultorio_pdf', compact('acta', 'detalle', 'contenido'));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->stream("Consultorio_{$slug}_Acta_{$id}.pdf");
    }

    /**
     * Elimina un consultorio dinámico de un acta de monitoreo.
     */
    public function destroyConsultorio($id, $slug)
    {
        try {
            DB::beginTransaction();

            MonitoreoModulos::where('cabecera_monitoreo_id', $id)
                ->where('modulo_nombre', $slug)
                ->delete();

            EquipoComputo::where('cabecera_monitoreo_id', $id)
                ->where('modulo', $slug)
                ->delete();

            DB::commit();

            return redirect()
                ->route('usuario.monitoreo.modulos', $id)
                ->with('success', 'Consultorio eliminado del acta correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Error al eliminar el consultorio: ' . $e->getMessage());
        }
    }

    /**
     * Mapeo legacy para compatibilidad con rutas estáticas si fuera necesario
     */
    private function resolveModuloSlug(Request $request, ?string $modulo): string
    {
        if (empty($modulo)) {
            $routeName = $request->route() ? $request->route()->getName() : '';
            $cleanName = str_replace(['usuario.monitoreo.', '.index', '.store', '.pdf'], '', $routeName);
            $modulo = $cleanName;
        }
        return str_replace('-', '_', $modulo);
    }

    public function show(Request $request, $id, $modulo = null)
    {
        return $this->showConsultorio($id, $this->resolveModuloSlug($request, $modulo));
    }

    public function store(Request $request, $id, $modulo = null)
    {
        return $this->storeConsultorio($request, $id, $this->resolveModuloSlug($request, $modulo));
    }

    public function pdf(Request $request, $id, $modulo = null)
    {
        return $this->pdfConsultorio($request, $id, $this->resolveModuloSlug($request, $modulo));
    }
}
