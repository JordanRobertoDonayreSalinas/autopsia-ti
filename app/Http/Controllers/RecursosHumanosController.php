<?php

namespace App\Http\Controllers;

use App\Models\CabeceraMonitoreo;
use App\Models\MonitoreoModulos;
use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class RecursosHumanosController extends Controller
{
    /**
     * Servicios estándar de un establecimiento de salud
     */
    private function getServiciosDisponibles(): array
    {
        return [
            'MEDICINA',
            'ODONTOLOGÍA',
            'ENFERMERÍA',
            'OBSTETRICIA',
            'PSICOLOGÍA',
            'NUTRICIÓN',
            'FARMACIA',
            'LABORATORIO',
            'TRIAJE',
            'URGENCIAS Y EMERGENCIAS',
            'TÓPICO',
            'CRED',
            'INMUNIZACIONES',
            'ADMISIÓN Y ARCHIVO',
            'GESTIÓN ADMINISTRATIVA',
            'OTROS'
        ];
    }

    /**
     * Profesiones estándar de salud
     */
    private function getProfesionesDisponibles(): array
    {
        return [
            'MÉDICO CIRUJANO',
            'CIRUJANO DENTISTA / ODONTÓLOGO(A)',
            'LIC. EN ENFERMERÍA',
            'LIC. EN OBSTETRICIA',
            'LIC. EN PSICOLOGÍA',
            'LIC. EN NUTRICIÓN',
            'QUÍMICO FARMACÉUTICO(A)',
            'LIC. TECNOLOGÍA MÉDICA',
            'TÉCNICO(A) EN ENFERMERÍA',
            'TÉCNICO(A) EN FARMACIA',
            'TÉCNICO(A) EN LABORATORIO',
            'PERSONAL ADMINISTRATIVO',
            'OTROS'
        ];
    }

    /**
     * Genera lista de periodos SERUMS basados en el año actual
     * Ejemplo para 2026: ['2025-2', '2026-1', '2026-2', '2027-1']
     */
    private function getPeriodosSerums(): array
    {
        $currentYear = (int) date('Y');
        $periodos = [];

        // Año anterior semestre 2
        $periodos[] = ($currentYear - 1) . '-2';
        // Año actual semestre 1 y 2
        $periodos[] = $currentYear . '-1';
        $periodos[] = $currentYear . '-2';
        // Siguiente año semestre 1
        $periodos[] = ($currentYear + 1) . '-1';

        return $periodos;
    }

    /**
     * Muestra la vista principal de RR.HH para un acta de monitoreo
     */
    public function index($id)
    {
        $acta = CabeceraMonitoreo::with('establecimiento')->findOrFail($id);

        $detalle = MonitoreoModulos::firstOrCreate(
            [
                'cabecera_monitoreo_id' => $id,
                'modulo_nombre'         => 'rrhh',
            ],
            [
                'contenido' => [
                    'trabajadores' => [],
                    'observaciones' => '',
                ]
            ]
        );

        $contenido = $detalle->contenido ?? [];
        $trabajadores = $contenido['trabajadores'] ?? [];
        $servicios = $this->getServiciosDisponibles();
        $profesiones = $this->getProfesionesDisponibles();
        $periodosSerums = $this->getPeriodosSerums();

        return view('usuario.monitoreo.modulos.rrhh', compact(
            'acta',
            'detalle',
            'trabajadores',
            'servicios',
            'profesiones',
            'periodosSerums'
        ));
    }

    /**
     * Guarda / actualiza el padrón completo de trabajadores de RR.HH
     */
    public function store(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $acta = CabeceraMonitoreo::findOrFail($id);
            $detalle = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
                ->where('modulo_nombre', 'rrhh')
                ->firstOrFail();

            $contenido = $detalle->contenido ?? [];
            $trabajadores = $request->input('trabajadores', []);

            // Normalizar array de trabajadores
            $trabajadoresNormalizados = [];
            foreach ($trabajadores as $index => $t) {
                if (empty($t['doc']) && empty($t['nombres']) && empty($t['apellido_paterno'])) {
                    continue;
                }

                $doc = trim($t['doc'] ?? '');
                $tipoDoc = strtoupper(trim($t['tipo_doc'] ?? 'DNI'));
                $paterno = mb_strtoupper(trim($t['apellido_paterno'] ?? ''));
                $materno = mb_strtoupper(trim($t['apellido_materno'] ?? ''));
                $nombres = mb_strtoupper(trim($t['nombres'] ?? ''));
                $servicio = mb_strtoupper(trim($t['servicio'] ?? 'MEDICINA'));
                $profesion = mb_strtoupper(trim($t['profesion'] ?? ''));
                $colegiatura = trim($t['colegiatura'] ?? '');
                $correo = strtolower(trim($t['correo'] ?? ''));
                $celular = trim($t['celular'] ?? '');
                $rne = trim($t['rne'] ?? '');
                $esSerums = strtoupper(trim($t['es_serums'] ?? 'NO')) === 'SI' ? 'SI' : 'NO';
                $periodoSerums = $esSerums === 'SI' ? trim($t['periodo_serums'] ?? '') : '';

                $trabajadorData = [
                    'id'               => $t['id'] ?? ('tr_' . time() . '_' . $index),
                    'servicio'         => $servicio,
                    'tipo_doc'         => $tipoDoc,
                    'doc'              => $doc,
                    'apellido_paterno' => $paterno,
                    'apellido_materno' => $materno,
                    'nombres'          => $nombres,
                    'profesion'        => $profesion,
                    'colegiatura'      => $colegiatura,
                    'correo'           => $correo,
                    'celular'          => $celular,
                    'rne'              => $rne,
                    'es_serums'        => $esSerums,
                    'periodo_serums'   => $periodoSerums,
                ];

                $trabajadoresNormalizados[] = $trabajadorData;

                // Sincronizar en maestro global de profesionales si tiene documento
                if (!empty($doc)) {
                    Profesional::updateOrCreate(
                        ['doc' => $doc],
                        [
                            'tipo_doc'         => $tipoDoc,
                            'nombres'          => $nombres,
                            'apellido_paterno' => $paterno,
                            'apellido_materno' => $materno,
                            'cargo'            => $profesion,
                            'email'            => $correo,
                            'telefono'         => $celular,
                        ]
                    );
                }
            }

            $contenido['trabajadores'] = $trabajadoresNormalizados;
            $contenido['observaciones'] = $request->input('observaciones', $contenido['observaciones'] ?? '');
            $contenido['total_trabajadores'] = count($trabajadoresNormalizados);
            $contenido['fecha_actualizacion'] = date('Y-m-d H:i:s');

            // Procesar Foto 1 (Reemplazo / Eliminación / Mantenimiento)
            $foto1Anterior = $detalle->contenido['foto_1'] ?? null;
            if ($request->hasFile('foto_1')) {
                if ($foto1Anterior && Storage::disk('public')->exists($foto1Anterior)) {
                    Storage::disk('public')->delete($foto1Anterior);
                }
                $path1 = $request->file('foto_1')->store('evidencias_rrhh', 'public');
                $contenido['foto_1'] = $path1;
            } else {
                $foto1Actual = $request->input('foto_1_actual');
                if (empty($foto1Actual)) {
                    if ($foto1Anterior && Storage::disk('public')->exists($foto1Anterior)) {
                        Storage::disk('public')->delete($foto1Anterior);
                    }
                    $contenido['foto_1'] = null;
                } else {
                    $contenido['foto_1'] = $foto1Actual;
                }
            }

            // Procesar Foto 2 (Reemplazo / Eliminación / Mantenimiento)
            $foto2Anterior = $detalle->contenido['foto_2'] ?? null;
            if ($request->hasFile('foto_2')) {
                if ($foto2Anterior && Storage::disk('public')->exists($foto2Anterior)) {
                    Storage::disk('public')->delete($foto2Anterior);
                }
                $path2 = $request->file('foto_2')->store('evidencias_rrhh', 'public');
                $contenido['foto_2'] = $path2;
            } else {
                $foto2Actual = $request->input('foto_2_actual');
                if (empty($foto2Actual)) {
                    if ($foto2Anterior && Storage::disk('public')->exists($foto2Anterior)) {
                        Storage::disk('public')->delete($foto2Anterior);
                    }
                    $contenido['foto_2'] = null;
                } else {
                    $contenido['foto_2'] = $foto2Actual;
                }
            }

            $detalle->update(['contenido' => $contenido]);

            DB::commit();

            return redirect()->route('usuario.monitoreo.rrhh.index', $id)
                ->with('success', 'Padrón de RR.HH guardado correctamente con ' . count($trabajadoresNormalizados) . ' trabajador(es).');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al guardar RR.HH: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al guardar los datos de RR.HH: ' . $e->getMessage());
        }
    }

    /**
     * Genera el Reporte PDF de Recursos Humanos del establecimiento
     */
    public function pdf($id)
    {
        $acta = CabeceraMonitoreo::with('establecimiento')->findOrFail($id);

        $detalle = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', 'rrhh')
            ->first();

        $contenido = $detalle->contenido ?? [];
        $trabajadores = $contenido['trabajadores'] ?? [];

        $pdf = Pdf::loadView('usuario.monitoreo.pdf.rrhh_pdf', compact('acta', 'detalle', 'contenido', 'trabajadores'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->stream("Reporte_RRHH_Acta_{$acta->numero_acta}.pdf");
    }
}
