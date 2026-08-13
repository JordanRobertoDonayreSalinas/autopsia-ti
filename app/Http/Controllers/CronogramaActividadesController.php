<?php

namespace App\Http\Controllers;

use App\Models\Reunion;
use App\Models\CabeceraMonitoreo;
use App\Models\Establecimiento;
use App\Exports\CronogramaActividadesExport;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class CronogramaActividadesController extends Controller
{
    /**
     * Recopila todas las actividades aplicando filtros dados (Monitoreo y Reuniones).
     * Retorna colección ordenada por fecha DESC.
     */
    protected function recopilarActividades(
        string $fechaInicio,
        string $fechaFin,
        ?string $filtroProv,
        ?string $filtroTipo,
        bool $enriquecido = false  // true => carga relaciones extra para el Excel
    ) {
        $actividades = collect();

        // ============================================================
        // 1. ACTAS DE MONITOREO
        // ============================================================
        if (!$filtroTipo || $filtroTipo === 'monitoreo') {
            $with = $enriquecido ? ['establecimiento', 'equipo', 'detalles'] : ['establecimiento'];

            $queryMon = CabeceraMonitoreo::with($with)
                ->where(fn($q) => $q->where('anulado', false)->orWhereNull('anulado'))
                ->whereDate('fecha', '>=', $fechaInicio)
                ->whereDate('fecha', '<=', $fechaFin);

            if ($filtroProv) {
                $queryMon->whereHas('establecimiento', fn($q) => $q->where('provincia', $filtroProv));
            }

            $queryMon->get()->each(function ($acta) use (&$actividades, $enriquecido) {

                $participantesTxt = '—';
                $imagenesPaths = [];

                if ($enriquecido) {
                    $lineas = [];

                    $cargoMayus = fn(string $s): string => mb_strtoupper(trim($s));
                    $titleCase = fn(string $s): string =>
                        mb_convert_case(mb_strtolower(trim($s)), MB_CASE_TITLE, 'UTF-8');

                    if ($acta->implementador) {
                        $lineas[] = 'IMPLEMENTADOR: ' . $titleCase($acta->implementador);
                    }

                    foreach ($acta->equipo as $miembro) {
                        $nombre = trim(
                            ($miembro->apellido_paterno ?? '') . ' ' .
                            ($miembro->apellido_materno ?? '') . ' ' .
                            ($miembro->nombres ?? '')
                        );
                        if ($nombre) {
                            $cargoFmt = $miembro->cargo ? $cargoMayus($miembro->cargo) . ': ' : '';
                            $lineas[] = $cargoFmt . $titleCase($nombre);
                        }
                    }

                    foreach ($acta->detalles as $modulo) {
                        $contenido = $modulo->contenido;
                        if (empty($contenido)) continue;

                        $prof = $contenido['profesional']
                             ?? $contenido['datos_del_profesional']
                             ?? null;

                        if (!$prof) continue;

                        $nombre = trim(
                            ($prof['apellido_paterno'] ?? '') . ' ' .
                            ($prof['apellido_materno'] ?? '') . ' ' .
                            ($prof['nombres']          ?? '')
                        );
                        if (!$nombre) continue;

                        $cargo = trim(
                            $prof['cargo']     ??
                            $prof['profesion'] ??
                            ''
                        );
                        $cargoFmt = $cargo ? $cargoMayus($cargo) . ': ' : '';
                        $lineas[] = $cargoFmt . $titleCase($nombre);
                    }

                    $participantesTxt = implode("\n", $lineas) ?: '—';

                    foreach (['foto1','foto2'] as $campo) {
                        if (!empty($acta->$campo)) {
                            $path = storage_path('app/public/' . $acta->$campo);
                            if (file_exists($path)) {
                                $imagenesPaths[] = $path;
                            }
                        }
                    }
                }

                $actividades->push([
                    'fecha'                     => $acta->fecha,
                    'tipo'                      => 'Monitoreo',
                    'tipo_key'                  => 'monitoreo',
                    'establecimiento'           => optional($acta->establecimiento)->nombre ?? '—',
                    'categoria_establecimiento' => optional($acta->establecimiento)->categoria ?? '',
                    'provincia'                 => optional($acta->establecimiento)->provincia ?? '—',
                    'responsable'               => $acta->implementador ?? '—',
                    'actividad'                 => $acta->tipo_origen ?? '—',
                    'modalidad'                 => '—',
                    'firmado'                   => $acta->firmado,
                    'anulado'                   => $acta->anulado ?? false,
                    'nombre_acta'               => 'Acta de Monitoreo N° ' . ($acta->numero_acta ?? $acta->id),
                    'num_acta'                  => $acta->numero_acta ?? $acta->id,
                    'participantes_txt'         => $participantesTxt,
                    'imagenes_paths'            => $imagenesPaths,
                ]);
            });
        }

        // ============================================================
        // 2. ACTAS DE REUNIÓN
        // ============================================================
        if (!$filtroTipo || $filtroTipo === 'reunion') {
            $queryReu = Reunion::query()
                ->where(fn($q) => $q->where('anulado', false)->orWhereNull('anulado'))
                ->whereDate('fecha_reunion', '>=', $fechaInicio)
                ->whereDate('fecha_reunion', '<=', $fechaFin);

            $queryReu->get()->each(function ($reunion) use (&$actividades, $enriquecido) {

                $participantesTxt = '—';
                $imagenesPaths    = [];

                if ($enriquecido) {
                    $lineas = [];

                    $cargoMayus = fn(string $s): string => mb_strtoupper(trim($s));
                    $titleCase  = fn(string $s): string =>
                        mb_convert_case(mb_strtolower(trim($s)), MB_CASE_TITLE, 'UTF-8');

                    $participantesJson = $reunion->participantes ?? [];
                    foreach ($participantesJson as $p) {
                        $nombre = trim(($p['apellidos'] ?? '') . ' ' . ($p['nombres'] ?? ''));
                        if ($nombre) {
                            $cargoFmt = !empty(trim($p['cargo'] ?? ''))
                                ? $cargoMayus($p['cargo']) . ': '
                                : '';
                            $lineas[] = $cargoFmt . $titleCase($nombre);
                        }
                    }

                    $participantesTxt = implode("\n", $lineas) ?: '—';

                    foreach (['foto_1', 'foto_2'] as $campo) {
                        if (!empty($reunion->$campo)) {
                            $relativePath = str_replace('storage/', '', $reunion->$campo);
                            $path = storage_path('app/public/' . $relativePath);
                            if (file_exists($path)) {
                                $imagenesPaths[] = $path;
                            }
                        }
                    }
                }

                $actividades->push([
                    'fecha'                     => $reunion->fecha_reunion,
                    'tipo'                      => 'Acta de Reunión',
                    'tipo_key'                  => 'reunion',
                    'establecimiento'           => $reunion->nombre_institucion ?? '—',
                    'categoria_establecimiento' => '',
                    'provincia'                 => '—',
                    'responsable'               => '—',
                    'actividad'                 => $reunion->titulo_reunion ?? '—',
                    'modalidad'                 => '—',
                    'firmado'                   => false,
                    'anulado'                   => $reunion->anulado ?? false,
                    'nombre_acta'               => 'Locación Presencial',
                    'num_acta'                  => $reunion->id,
                    'participantes_txt'         => $participantesTxt,
                    'imagenes_paths'            => $imagenesPaths,
                ]);
            });
        }

        return $actividades->sortByDesc('fecha')->values();
    }

    /**
     * Muestra el Cronograma de Actividades unificado.
     */
    public function index(Request $request)
    {
        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            session([
                'cronograma_fecha_inicio' => $request->input('fecha_inicio'),
                'cronograma_fecha_fin'    => $request->input('fecha_fin'),
            ]);
        }

        $fechaInicio = session('cronograma_fecha_inicio')
            ?? $request->input('fecha_inicio', Carbon::now()->startOfMonth()->toDateString());

        $fechaFin = session('cronograma_fecha_fin')
            ?? $request->input('fecha_fin', Carbon::now()->endOfMonth()->toDateString());

        $filtroProv = $request->input('provincia');
        $filtroTipo = $request->input('tipo_acta');

        $todasActividades = $this->recopilarActividades($fechaInicio, $fechaFin, $filtroProv, $filtroTipo);

        $perPage     = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemsSlice  = $todasActividades->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $actividadesPaginadas = new LengthAwarePaginator(
            $itemsSlice,
            $todasActividades->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $countMonitoreo  = $todasActividades->where('tipo_key', 'monitoreo')->count();
        $countReunion    = $todasActividades->where('tipo_key', 'reunion')->count();
        $countTotal      = $todasActividades->count();

        $provincias = Establecimiento::distinct()
            ->whereNotNull('provincia')
            ->where('provincia', '!=', '')
            ->orderBy('provincia')
            ->pluck('provincia');

        return view('usuario.reportes.cronograma_actividades', compact(
            'actividadesPaginadas',
            'fechaInicio',
            'fechaFin',
            'filtroProv',
            'filtroTipo',
            'provincias',
            'countMonitoreo',
            'countReunion',
            'countTotal'
        ));
    }

    public function exportarExcel(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->toDateString());
        $fechaFin    = $request->input('fecha_fin', Carbon::now()->endOfMonth()->toDateString());
        $filtroProv  = $request->input('provincia');
        $filtroTipo  = $request->input('tipo_acta');

        $actividades = $this->recopilarActividades($fechaInicio, $fechaFin, $filtroProv, $filtroTipo, true);

        return Excel::download(
            new CronogramaActividadesExport($actividades, $fechaInicio, $fechaFin),
            'Cronograma_Actividades_' . Carbon::now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportarPdf(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfMonth()->toDateString());
        $fechaFin    = $request->input('fecha_fin', Carbon::now()->endOfMonth()->toDateString());
        $filtroProv  = $request->input('provincia');
        $filtroTipo  = $request->input('tipo_acta');

        $actividades = $this->recopilarActividades($fechaInicio, $fechaFin, $filtroProv, $filtroTipo);

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L',
            'margin_left'   => 12,
            'margin_right'  => 12,
            'margin_top'    => 12,
            'margin_bottom' => 16,
        ]);

        $html = view('usuario.reportes.cronograma_pdf', compact(
            'actividades',
            'fechaInicio',
            'fechaFin',
            'filtroProv',
            'filtroTipo'
        ))->render();

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Cronograma_Actividades.pdf"',
        ]);
    }

    public function ajaxGetProvincias()
    {
        $provincias = Establecimiento::distinct()
            ->whereNotNull('provincia')
            ->where('provincia', '!=', '')
            ->orderBy('provincia')
            ->pluck('provincia');

        return response()->json($provincias);
    }
}
