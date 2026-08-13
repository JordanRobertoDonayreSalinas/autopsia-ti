<?php

namespace App\Http\Controllers;

use App\Models\MonitoreoModulos;
use App\Models\Establecimiento;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReporteConsultoriosMedicinaController extends Controller
{
    /**
     * Muestra el reporte de Consultorios de Medicina
     */
    public function index(Request $request)
    {
        // Persistencia de fechas en sesión
        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            session(['medicina_fecha_inicio' => $request->input('fecha_inicio')]);
            session(['medicina_fecha_fin'    => $request->input('fecha_fin')]);
        } else {
            $request->merge([
                'fecha_inicio' => session('medicina_fecha_inicio', now()->startOfYear()->format('Y-m-d')),
                'fecha_fin'    => session('medicina_fecha_fin',    now()->format('Y-m-d')),
            ]);
        }

        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin    = $request->input('fecha_fin');
        $provincia   = $request->input('provincia');
        $distrito    = $request->input('distrito');
        $categoria   = $request->input('categoria');
        $establecimiento_id = $request->input('establecimiento_id');

        // Query principal
        $query = $this->buildQuery($fechaInicio, $fechaFin, $provincia, $distrito, $categoria, $establecimiento_id);

        $registros = $query->get()->map(function($modulo) {
            $c = $modulo->contenido ?? [];
            $nombres = $c['profesional']['nombres'] ?? '';
            $paterno = $c['profesional']['apellido_paterno'] ?? '';
            $materno = $c['profesional']['apellido_materno'] ?? '';
            $profesional = trim("$nombres $paterno $materno");
            if (empty($profesional)) {
                $profesional = 'SIN PROFESIONAL REGISTRADO';
            }

            return [
                'id' => $modulo->id,
                'cabecera_id' => $modulo->cabecera->id ?? null,
                'acta_id' => $modulo->cabecera->numero_acta ?? 'N/A',
                'fecha_monitoreo' => $modulo->cabecera->fecha ?? '',
                'establecimiento_id' => $modulo->cabecera->establecimiento_id ?? '',
                'red' => $modulo->cabecera->establecimiento->red ?? '',
                'microred' => $modulo->cabecera->establecimiento->microred ?? '',
                'establecimiento' => $modulo->cabecera->establecimiento->nombre ?? '',
                'distrito' => $modulo->cabecera->establecimiento->distrito ?? '',
                'turno' => $c['turno'] ?? 'NO ESPECIFICADO',
                'num_consultorios' => $c['num_consultorios'] ?? '0',
                'denominacion_consultorio' => $c['denominacion_consultorio'] ?? 'NO ESPECIFICADO',
                'profesional_entrevistado' => $profesional,
            ];
        })->sortByDesc('fecha_monitoreo');

        if ($request->filled('ultima_visita') && $request->ultima_visita == '1') {
            $latestCabeceras = $registros->groupBy('establecimiento_id')->map(function($group) {
                return $group->first()['cabecera_id'];
            });
            $registros = $registros->whereIn('cabecera_id', $latestCabeceras->values())->values();
        }

        // Listas para filtros en cascada (carga inicial)
        $provincias = Establecimiento::distinct()->orderBy('provincia')->pluck('provincia')->filter();
        
        $queryDist = Establecimiento::distinct()->orderBy('distrito');
        if ($provincia) $queryDist->where('provincia', $provincia);
        $distritos = $queryDist->pluck('distrito')->filter();

        $queryCat = Establecimiento::distinct()->orderBy('categoria');
        if ($provincia) $queryCat->where('provincia', $provincia);
        if ($distrito) $queryCat->where('distrito', $distrito);
        $categorias = $queryCat->pluck('categoria')->filter();

        return view('usuario.reportes.consultorios_medicina', compact(
            'registros', 'fechaInicio', 'fechaFin', 'provincias', 'provincia', 'distritos', 'distrito', 'categorias', 'categoria', 'establecimiento_id'
        ));
    }

    /**
     * Exportar a Excel
     */
    public function exportarExcel(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', session('medicina_fecha_inicio', now()->startOfYear()->format('Y-m-d')));
        $fechaFin    = $request->input('fecha_fin', session('medicina_fecha_fin', now()->format('Y-m-d')));
        $provincia   = $request->input('provincia');
        $distrito    = $request->input('distrito');
        $categoria   = $request->input('categoria');
        $establecimiento_id = $request->input('establecimiento_id');

        $query = $this->buildQuery($fechaInicio, $fechaFin, $provincia, $distrito, $categoria, $establecimiento_id);

        $modulos = $query->get();

        if ($request->input('ultima_visita') == '1') {
            $modulos = $modulos->sortByDesc(function($modulo) {
                return $modulo->cabecera->fecha ?? '';
            });
            $latestCabeceras = $modulos->groupBy('cabecera.establecimiento_id')->map(function($group) {
                return $group->first()->cabecera->id ?? null;
            });
            $modulos = $modulos->filter(function($modulo) use ($latestCabeceras) {
                return in_array($modulo->cabecera->id ?? null, $latestCabeceras->values()->all());
            });
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Consultorios de Medicina');

        // Estilos
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4F46E5'] // Indigo-600
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']],
            ]
        ];

        $dataStyle = [
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']],
            ]
        ];

        // Encabezados
        $headers = [
            'A1' => 'ID ACTA',
            'B1' => 'FECHA MONITOREO',
            'C1' => 'DISTRITO',
            'D1' => 'RED',
            'E1' => 'MICRORED',
            'F1' => 'ESTABLECIMIENTO',
            'G1' => 'TURNO',
            'H1' => 'NRO CONSULTORIOS',
            'I1' => 'DENOMINACIÓN',
            'J1' => 'PROFESIONAL ENTREVISTADO'
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
            $sheet->getStyle($cell)->applyFromArray($headerStyle);
        }

        // Datos
        $row = 2;
        foreach ($modulos as $modulo) {
            $c = $modulo->contenido ?? [];
            $nombres = $c['profesional']['nombres'] ?? '';
            $paterno = $c['profesional']['apellido_paterno'] ?? '';
            $materno = $c['profesional']['apellido_materno'] ?? '';
            $profesional = trim("$nombres $paterno $materno");
            if (empty($profesional)) {
                $profesional = 'SIN PROFESIONAL REGISTRADO';
            }

            $sheet->setCellValue('A' . $row, $modulo->cabecera->numero_acta ?? 'N/A');
            $sheet->setCellValue('B' . $row, $modulo->cabecera->fecha ?? '');
            $sheet->setCellValue('C' . $row, $modulo->cabecera->establecimiento->distrito ?? '');
            $sheet->setCellValue('D' . $row, $modulo->cabecera->establecimiento->red ?? '');
            $sheet->setCellValue('E' . $row, $modulo->cabecera->establecimiento->microred ?? '');
            $sheet->setCellValue('F' . $row, $modulo->cabecera->establecimiento->nombre ?? '');
            $sheet->setCellValue('G' . $row, $c['turno'] ?? 'NO ESPECIFICADO');
            $sheet->setCellValue('H' . $row, $c['num_consultorios'] ?? '0');
            $sheet->setCellValue('I' . $row, $c['denominacion_consultorio'] ?? 'NO ESPECIFICADO');
            $sheet->setCellValue('J' . $row, $profesional);

            $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($dataStyle);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Reporte_Consultorios_Medicina_' . date('Ymd_His') . '.xlsx';

        // Headers response
        return response()->streamDownload(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0'
        ]);
    }

    /**
     * Construye la consulta base para el reporte.
     *
     * @param string|null $fechaInicio
     * @param string|null $fechaFin
     * @param string|null $provincia
     * @param string|null $distrito
     * @param string|null $categoria
     * @param string|null $establecimiento_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildQuery($fechaInicio, $fechaFin, $provincia, $distrito, $categoria, $establecimiento_id)
    {
        return MonitoreoModulos::with(['cabecera.establecimiento'])
            ->where('modulo_nombre', 'consulta_medicina')
            ->whereHas('cabecera', function(\Illuminate\Database\Eloquent\Builder $q) use($fechaInicio, $fechaFin, $provincia, $distrito, $categoria, $establecimiento_id) {
                if ($fechaInicio && $fechaFin) {
                    $q->whereBetween('fecha', [$fechaInicio, $fechaFin]);
                }
                if ($provincia || $distrito || $categoria) {
                    $q->whereHas('establecimiento', function(\Illuminate\Database\Eloquent\Builder $q2) use($provincia, $distrito, $categoria) {
                        if ($provincia) {
                            $q2->where('provincia', $provincia);
                        }
                        if ($distrito) {
                            $q2->where('distrito', $distrito);
                        }
                        if ($categoria) {
                            $q2->where('categoria', $categoria);
                        }
                    });
                }
                if ($establecimiento_id) {
                    $q->where('establecimiento_id', $establecimiento_id);
                }
            });
    }
}
