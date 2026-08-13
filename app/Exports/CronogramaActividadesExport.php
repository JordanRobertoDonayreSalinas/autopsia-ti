<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class CronogramaActividadesExport implements WithEvents, WithTitle
{
    protected $actividades;

    const ROW_HEIGHT = 100;

    public function __construct($actividades)
    {
        $this->actividades = $actividades;
    }

    public function title(): string
    {
        return 'Cronograma de Actividades';
    }

    /**
     * Agrupa las actividades por mes y dentro de cada mes por semana.
     * Las semanas reinician en 1 al comenzar cada mes.
     * Semana 1: día 1 del mes → primer domingo (si día 1 es domingo se extiende al siguiente).
     * Semanas siguientes: lunes → domingo.
     *
     * Retorna: [
     *   ['mes' => 'Marzo 2026', 'semanas' => [
     *     ['semana'=>1, 'desde'=>Carbon, 'hasta'=>Carbon, 'filas'=>[...]],
     *     ...
     *   ]],
     *   ...
     * ]
     */
    protected function agruparPorMesYSemana(): array
    {
        if ($this->actividades->isEmpty()) {
            return [];
        }

        // Agrupar actividades por año-mes
        $porMes = [];
        foreach ($this->actividades as $fila) {
            $key = Carbon::parse($fila['fecha'])->format('Y-m');
            $porMes[$key][] = $fila;
        }
        ksort($porMes);

        $resultado = [];

        foreach ($porMes as $yearMonth => $filasDelMes) {
            [$year, $month] = explode('-', $yearMonth);
            $year  = (int) $year;
            $month = (int) $month;

            $cursor = Carbon::create($year, $month, 1)->startOfDay();
            $finMes = Carbon::create($year, $month, 1)->endOfMonth()->startOfDay();
            $numSem = 1;
            $semanas = [];

            while ($cursor->lte($finMes)) {
                $finSemana = $cursor->copy()->endOfWeek(Carbon::SUNDAY)->startOfDay();

                // Si día 1 es domingo, endOfWeek devuelve ese mismo día (semana de 1 día).
                // Extendemos al domingo de la semana siguiente.
                if ($finSemana->isSameDay($cursor)) {
                    $finSemana = $cursor->copy()->addWeek()->endOfWeek(Carbon::SUNDAY)->startOfDay();
                }

                if ($finSemana->gt($finMes)) {
                    $finSemana = $finMes->copy();
                }

                $semanas[] = [
                    'semana' => $numSem,
                    'desde'  => $cursor->copy(),
                    'hasta'  => $finSemana->copy(),
                    'filas'  => [],
                ];

                $cursor = $finSemana->copy()->addDay();
                $numSem++;
            }

            // Asignar cada actividad del mes a su semana
            foreach ($filasDelMes as $fila) {
                $fecha = Carbon::parse($fila['fecha'])->startOfDay();
                foreach ($semanas as &$semana) {
                    if ($fecha->between($semana['desde'], $semana['hasta'])) {
                        $semana['filas'][] = $fila;
                        break;
                    }
                }
                unset($semana);
            }

            // Quitar semanas vacías
            $semanas = array_values(array_filter($semanas, fn($s) => count($s['filas']) > 0));

            if (count($semanas) > 0) {
                $resultado[] = [
                    'mes'     => mb_strtoupper(Carbon::create($year, $month, 1)->locale('es')->isoFormat('MMMM YYYY'), 'UTF-8'),
                    'semanas' => $semanas,
                ];
            }
        }

        return $resultado;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ============================================================
                // CABECERA (fila 1) — A=SEMANAS, B=FECHA, C=DESCRIPCIÓN,
                // D=ESTABLECIMIENTO, E=PARTICIPANTES, F=ACTA, G=EVIDENCIA
                // ============================================================
                $headers = [
                    'A1' => 'SEMANAS',
                    'B1' => 'FECHA DE ACTIVIDAD',
                    'C1' => 'DESCRIPCIÓN DE ACTIVIDAD',
                    'D1' => 'ESTABLECIMIENTO EN DONDE SE REALIZA LA ACTIVIDAD',
                    'E1' => 'PARTICIPANTES EN LA ACTIVIDAD',
                    'F1' => 'ACTA O EVIDENCIA DE LA ACTIVIDAD REALIZADA',
                ];

                foreach ($headers as $cell => $value) {
                    $sheet->setCellValue($cell, $value);
                }
                $sheet->mergeCells('F1:G1');

                $headerStyle = [
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10, 'name' => 'Calibri'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]],
                ];
                $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
                $sheet->getRowDimension(1)->setRowHeight(36);

                // Anchos de columna
                $sheet->getColumnDimension('A')->setWidth(20);
                $sheet->getColumnDimension('B')->setWidth(14);
                $sheet->getColumnDimension('C')->setWidth(45);
                $sheet->getColumnDimension('D')->setWidth(38);
                $sheet->getColumnDimension('E')->setWidth(48);
                $sheet->getColumnDimension('F')->setWidth(40);
                $sheet->getColumnDimension('G')->setWidth(46);

                // ============================================================
                // ESTILOS REUTILIZABLES
                // ============================================================
                $cellStyle = [
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]],
                    'font'      => ['size' => 9, 'name' => 'Calibri'],
                ];

                $semanaStyle = [
                    'font'      => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 10, 'name' => 'Calibri'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]],
                ];

                // Estilo fila de mes (cabecera separadora entre meses)
                $mesStyle = [
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10, 'name' => 'Calibri'],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E6DA4']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1E3A5F']]],
                ];

                // ============================================================
                // FILAS DE DATOS por mes → semana
                // ============================================================
                $rowNum = 2;
                $meses  = $this->agruparPorMesYSemana();

                foreach ($meses as $mesGrupo) {
                    // — Fila separadora de mes —
                    $sheet->setCellValue("A{$rowNum}", $mesGrupo['mes']);
                    $sheet->mergeCells("A{$rowNum}:G{$rowNum}");
                    $sheet->getStyle("A{$rowNum}:G{$rowNum}")->applyFromArray($mesStyle);
                    $sheet->getRowDimension($rowNum)->setRowHeight(22);
                    $rowNum++;

                    foreach ($mesGrupo['semanas'] as $grupo) {
                        $numSemana  = $grupo['semana'];
                        $filas      = $grupo['filas'];
                        $countFilas = count($filas);

                        $semanaLabel = "SEMANA {$numSemana}:\nDEL " . $grupo['desde']->format('d/m') . ' AL ' . $grupo['hasta']->format('d/m');
                        $grupoStartRow = $rowNum;

                        foreach ($filas as $fila) {
                            $fecha = Carbon::parse($fila['fecha'])->format('d/m/Y');

                            $sheet->setCellValue("B{$rowNum}", $fecha);

                            if ($fila['tipo_key'] === 'asistencia') {
                                $actividadTxt = $fila['actividad'] !== '—' ? $fila['actividad'] : '';
                                $actividadTxt = mb_strtolower($actividadTxt, 'UTF-8');
                                $actividadTxt = mb_strtoupper(mb_substr($actividadTxt, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($actividadTxt, 1, null, 'UTF-8');
                                $descripcion  = 'Asistencia Técnica: ' . $actividadTxt;
                                if (!empty($fila['modalidad']) && $fila['modalidad'] !== '—') {
                                    $descripcion .= "\nModalidad: " . $fila['modalidad'];
                                }
                            } elseif ($fila['tipo_key'] === 'monitoreo') {
                                $descripcion = 'Monitoreo de Uso del SIHCE MINSA - Presencial';
                            } elseif ($fila['tipo_key'] === 'reunion') {
                                $actividadTxt = $fila['actividad'] !== '—' ? $fila['actividad'] : '';
                                $actividadTxt = mb_convert_case(mb_strtolower($actividadTxt, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                                $descripcion  = $actividadTxt;
                            } else {
                                $descripcion = 'Implementación Módulo de ' . $fila['actividad'];
                            }
                            $sheet->setCellValue("C{$rowNum}", $descripcion);

                            if ($fila['tipo_key'] === 'reunion') {
                                // Para reuniones: establecimiento = nombre_institucion tal como viene (ya en mayúsculas)
                                $nombreEstab = $fila['establecimiento'];
                                if (mb_strtoupper($nombreEstab, 'UTF-8') === $nombreEstab) {
                                    $nombreEstab = mb_convert_case($nombreEstab, MB_CASE_TITLE, 'UTF-8');
                                }
                                $sheet->setCellValue("D{$rowNum}", $nombreEstab);
                            } else {
                                $categoria   = strtoupper(trim($fila['categoria_establecimiento'] ?? ''));
                                $prefijo     = in_array($categoria, ['I-1', 'I-2']) ? 'P.S.' : (in_array($categoria, ['I-3', 'I-4']) ? 'C.S.' : '');
                                $nombreEstab = $fila['establecimiento'];
                                if (mb_strtoupper($nombreEstab, 'UTF-8') === $nombreEstab) {
                                    $nombreEstab = mb_convert_case($nombreEstab, MB_CASE_TITLE, 'UTF-8');
                                }
                                $sheet->setCellValue("D{$rowNum}", ($prefijo ? $prefijo . ' ' : '') . $nombreEstab . ' - ' . $fila['provincia']);
                            }

                            $sheet->setCellValue("E{$rowNum}", $fila['participantes_txt'] ?? $fila['responsable']);

                            if ($fila['tipo_key'] === 'reunion') {
                                // Columna F: "Locación Presencial"
                                $sheet->setCellValue("F{$rowNum}", $fila['nombre_acta'] ?? 'Locación Presencial');
                                // Columna G: fotos del acta de reunión
                                $offsetX = 4;
                                if (!empty($fila['imagenes_paths']) && is_array($fila['imagenes_paths'])) {
                                    foreach ($fila['imagenes_paths'] as $imgPath) {
                                        try {
                                            $drawing = new Drawing();
                                            $drawing->setPath($imgPath);
                                            $drawing->setCoordinates("G{$rowNum}");
                                            $drawing->setOffsetX($offsetX);
                                            $drawing->setOffsetY(8);
                                            $drawing->setWidth(160);
                                            $drawing->setHeight(80);
                                            $drawing->setWorksheet($sheet);
                                            $offsetX += 166;
                                        } catch (\Exception $e) {}
                                    }
                                }
                            } else {
                                $sheet->setCellValue("F{$rowNum}", $fila['nombre_acta'] ?? ('Acta de ' . $fila['tipo']));

                                $offsetX = 4;
                                if (!empty($fila['imagenes_paths']) && is_array($fila['imagenes_paths'])) {
                                    foreach ($fila['imagenes_paths'] as $imgPath) {
                                        try {
                                            $drawing = new Drawing();
                                            $drawing->setPath($imgPath);
                                            $drawing->setCoordinates("G{$rowNum}");
                                            $drawing->setOffsetX($offsetX);
                                            $drawing->setOffsetY(8);
                                            $drawing->setWidth(160);
                                            $drawing->setHeight(80);
                                            $drawing->setWorksheet($sheet);
                                            $offsetX += 166;
                                        } catch (\Exception $e) {}
                                    }
                                }
                            }

                            $sheet->getRowDimension($rowNum)->setRowHeight(self::ROW_HEIGHT);
                            $sheet->getStyle("A{$rowNum}:G{$rowNum}")->applyFromArray($cellStyle);
                            $sheet->getStyle("C{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
                            $sheet->getStyle("E{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);

                            $rowNum++;
                        }

                        // Fusionar y estilar la columna SEMANA para este grupo
                        $grupoEndRow = $rowNum - 1;
                        $sheet->setCellValue("A{$grupoStartRow}", $semanaLabel);
                        if ($countFilas > 1) {
                            $sheet->mergeCells("A{$grupoStartRow}:A{$grupoEndRow}");
                        }
                        $sheet->getStyle("A{$grupoStartRow}:A{$grupoEndRow}")->applyFromArray($semanaStyle);
                    }
                }

                $sheet->freezePane('B2');
                $sheet->getSheetView()->setZoomScale(80);
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
            },
        ];
    }
}
