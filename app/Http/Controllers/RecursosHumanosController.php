<?php

namespace App\Http\Controllers;

use App\Models\CabeceraMonitoreo;
use App\Models\MonitoreoModulos;
use App\Models\Profesional;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
            'OTROS',
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
            'OTROS',
        ];
    }

    /**
     * Genera lista de periodos SERUMS basados en el año y mes actual.
     * Según el cronograma SERUMS (exactamente 3 opciones activas):
     * - De Enero a Septiembre (mes < 10): (Año-1)-1, (Año-1)-2 y Año-1 (Ej: en 2026 -> 2025-1, 2025-2, 2026-1).
     * - De Octubre a Diciembre (mes >= 10): se activa el nuevo periodo y se desactiva el primero: (Año-1)-2, Año-1 y Año-2 (Ej: en 2026 -> 2025-2, 2026-1, 2026-2).
     */
    private function getPeriodosSerums(): array
    {
        $year = (int) date('Y');
        $month = (int) date('n');

        if ($month < 10) {
            return [
                ($year - 1).'-1',
                ($year - 1).'-2',
                $year.'-1',
            ];
        }

        return [
            ($year - 1).'-2',
            $year.'-1',
            $year.'-2',
        ];
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
                'modulo_nombre' => 'rrhh',
            ],
            [
                'contenido' => [
                    'trabajadores' => [],
                    'observaciones' => '',
                ],
            ]
        );

        $contenido = $detalle->contenido ?? [];
        $trabajadores = $contenido['trabajadores'] ?? [];
        $servicios = $this->getServiciosDisponibles();
        $profesiones = $this->getProfesionesDisponibles();
        $periodosSerums = $this->getPeriodosSerums();

        // Formato nuevo: contenido['evidencias'] = [['path'=>...,'descripcion'=>...], ...]
        // (mismo formato que usan los consultorios dinámicos). Si no existe
        // todavía, se migra desde el formato viejo (foto_1/foto_2, 2 casillas
        // fijas sin descripción) para no perder evidencia ya cargada.
        $evidencias = [];
        if (!empty($contenido['evidencias']) && is_array($contenido['evidencias'])) {
            $evidencias = $contenido['evidencias'];
        } else {
            foreach (['foto_1', 'foto_2'] as $campoViejo) {
                if (!empty($contenido[$campoViejo])) {
                    $evidencias[] = ['path' => $contenido[$campoViejo], 'descripcion' => ''];
                }
            }
        }

        return view('usuario.monitoreo.modulos.rrhh', compact(
            'acta',
            'detalle',
            'trabajadores',
            'servicios',
            'profesiones',
            'periodosSerums',
            'evidencias'
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
                $colegioProfesional = mb_strtoupper(trim($t['colegio_profesional'] ?? ''));
                $rawColegiatura = trim($t['colegiatura'] ?? '');
                $colegiaturaDigits = preg_replace('/\D/', '', $rawColegiatura);
                $colegiatura = ! empty($colegiaturaDigits) ? str_pad(substr($colegiaturaDigits, 0, 6), 6, '0', STR_PAD_LEFT) : '';

                // Fallback para colegio_profesional si está vacío
                if (empty($colegioProfesional)) {
                    if (preg_match('/^([A-Za-z\.]+)\s*\d+/', $rawColegiatura, $matches)) {
                        $colegioProfesional = mb_strtoupper(trim($matches[1]));
                    } else {
                        $mapaPrefijos = [
                            'MÉDICO CIRUJANO' => 'CMP',
                            'CIRUJANO DENTISTA / ODONTÓLOGO(A)' => 'COP',
                            'LIC. EN ENFERMERÍA' => 'CEP',
                            'LIC. EN OBSTETRICIA' => 'COP',
                            'LIC. EN PSICOLOGÍA' => 'C.Ps.P',
                            'LIC. EN NUTRICIÓN' => 'CNP',
                            'QUÍMICO FARMACÉUTICO(A)' => 'CQFP',
                            'LIC. TECNOLOGÍA MÉDICA' => 'CTMP',
                            'BIÓLOGO(A)' => 'CBP',
                        ];
                        $colegioProfesional = $mapaPrefijos[$profesion] ?? '';
                    }
                }

                $correo = strtolower(trim($t['correo'] ?? ''));
                $celular = trim($t['celular'] ?? '');
                $rne = trim($t['rne'] ?? '');
                $tieneDnie = strtoupper(trim($t['tiene_dnie'] ?? 'NO')) === 'SI' ? 'SI' : 'NO';
                $versionDnie = $tieneDnie === 'SI' ? trim($t['version_dnie'] ?? 'v2.0') : '';
                $esSerums = strtoupper(trim($t['es_serums'] ?? 'NO')) === 'SI' ? 'SI' : 'NO';
                $periodoSerums = $esSerums === 'SI' ? trim($t['periodo_serums'] ?? '') : '';

                $trabajadorData = [
                    'id' => $t['id'] ?? ('tr_'.time().'_'.$index),
                    'servicio' => $servicio,
                    'tipo_doc' => $tipoDoc,
                    'doc' => $doc,
                    'apellido_paterno' => $paterno,
                    'apellido_materno' => $materno,
                    'nombres' => $nombres,
                    'profesion' => $profesion,
                    'colegio_profesional' => $colegioProfesional,
                    'colegiatura'         => $colegiatura,
                    'correo'              => $correo,
                    'celular'             => $celular,
                    'rne'                 => $rne,
                    'tiene_dnie'          => $tieneDnie,
                    'version_dnie'        => $versionDnie,
                    'es_serums'           => $esSerums,
                    'periodo_serums'      => $periodoSerums,
                ];

                $trabajadoresNormalizados[] = $trabajadorData;

                // Sincronizar en maestro global de profesionales si tiene documento
                if (! empty($doc)) {
                    Profesional::updateOrCreate(
                        ['doc' => $doc],
                        [
                            'tipo_doc' => $tipoDoc,
                            'nombres' => $nombres,
                            'apellido_paterno' => $paterno,
                            'apellido_materno' => $materno,
                            'cargo' => $profesion,
                            'email' => $correo,
                            'telefono' => $celular,
                        ]
                    );
                }
            }

            $contenido['trabajadores'] = $trabajadoresNormalizados;
            $contenido['observaciones'] = $request->input('observaciones', $contenido['observaciones'] ?? '');
            $contenido['total_trabajadores'] = count($trabajadoresNormalizados);
            $contenido['fecha_actualizacion'] = date('Y-m-d H:i:s');

            // Evidencia fotográfica: mismo formato de lista contenido['evidencias'] =
            // [['path'=>...,'descripcion'=>...], ...] que usan los consultorios
            // dinámicos (hasta 10 fotos, con QR desde el celular). Las fotos viejas
            // en foto_1/foto_2 (formato anterior, 2 casillas fijas sin descripción)
            // se migran una sola vez para no perderlas.
            $evidenciasAnteriores = [];
            if (!empty($detalle->contenido['evidencias']) && is_array($detalle->contenido['evidencias'])) {
                $evidenciasAnteriores = $detalle->contenido['evidencias'];
            } else {
                foreach (['foto_1', 'foto_2'] as $campoViejo) {
                    if (!empty($detalle->contenido[$campoViejo])) {
                        $evidenciasAnteriores[] = ['path' => $detalle->contenido[$campoViejo], 'descripcion' => ''];
                    }
                }
            }
            $pathsAnteriores = array_filter(array_column($evidenciasAnteriores, 'path'));

            $evidenciasInput = $request->input('evidencias', []);
            $evidenciasFiles = $request->file('evidencias', []);
            $evidenciasFinal = [];

            if (is_array($evidenciasInput)) {
                foreach ($evidenciasInput as $idx => $ev) {
                    if (count($evidenciasFinal) >= 10) {
                        break;
                    }

                    $descripcion = mb_strtoupper(trim($ev['descripcion'] ?? ''));
                    $pathExistente = $ev['path_existente'] ?? null;
                    $archivoNuevo = $evidenciasFiles[$idx]['foto'] ?? null;

                    if ($archivoNuevo instanceof \Illuminate\Http\UploadedFile) {
                        if ($pathExistente && Storage::disk('public')->exists($pathExistente)) {
                            Storage::disk('public')->delete($pathExistente);
                        }
                        $extension = strtolower($archivoNuevo->getClientOriginalExtension() ?: 'jpg');
                        $numFoto = count($evidenciasFinal) + 1;
                        $nombreEstandar = "evidencia_acta_{$id}_rrhh_{$numFoto}_".date('Ymd_His').'_'.uniqid().'.'.$extension;
                        $path = $archivoNuevo->storeAs('evidencias_rrhh', $nombreEstandar, 'public');
                        $evidenciasFinal[] = ['path' => $path, 'descripcion' => $descripcion];
                    } elseif ($pathExistente) {
                        $evidenciasFinal[] = ['path' => $pathExistente, 'descripcion' => $descripcion];
                    }
                }
            }

            $pathsFinal = array_column($evidenciasFinal, 'path');
            foreach ($pathsAnteriores as $pOld) {
                if (!in_array($pOld, $pathsFinal, true) && Storage::disk('public')->exists($pOld)) {
                    Storage::disk('public')->delete($pOld);
                }
            }

            $contenido['evidencias'] = $evidenciasFinal;
            unset($contenido['foto_1'], $contenido['foto_2']);

            $detalle->update(['contenido' => $contenido]);

            DB::commit();

            // Al guardar, se cierra el código QR de evidencia móvil activo (si lo
            // hay) para este RR.HH, igual que en los consultorios dinámicos.
            app(EvidenciaMovilController::class)->cerrarActivo($id, 'rrhh');

            return redirect()->route('usuario.monitoreo.rrhh.index', $id)
                ->with('success', 'Padrón de RR.HH guardado correctamente con '.count($trabajadoresNormalizados).' trabajador(es).');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al guardar RR.HH: '.$e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al guardar los datos de RR.HH: '.$e->getMessage());
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

        $contenido = $detalle ? $detalle->contenido : [];
        if (is_string($contenido)) {
            $contenido = json_decode($contenido, true) ?? [];
        }
        $trabajadores = $contenido['trabajadores'] ?? [];

        $pdf = Pdf::setOptions([
            'isPhpEnabled' => true,
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ])->loadView('usuario.monitoreo.pdf.rrhh_pdf', compact('acta', 'detalle', 'contenido', 'trabajadores'));
        $pdf->setPaper('a4', 'landscape');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Reporte_RRHH_Acta_'.$acta->numero_acta.'.pdf"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sun, 02 Jan 1990 00:00:00 GMT',
        ]);
    }
}
