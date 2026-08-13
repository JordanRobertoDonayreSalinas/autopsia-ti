<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ActasAsistenciaExport implements
    FromCollection, WithHeadings, WithMapping,
    WithStyles, WithColumnWidths, WithColumnFormatting
{
    protected $actas;

    public function __construct($actas)
    {
        $this->actas = $actas;
    }

    public function collection()
    {
        return $this->actas;
    }

    public function headings(): array
    {
        return [
            'N°',
            'Fecha',
            'Mes',
            'IPRESS',
            'Establecimiento',
            'Provincia',
            'Distrito',
            'Responsable',
            'Tema / Motivo',
            'Modalidad',
            'Implementador',
            'Módulos Atendidos',
            'N° Participantes',
            'Estado',
        ];
    }

    public function map($acta): array
    {
        $meses = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO',
            4 => 'ABRIL', 5 => 'MAYO', 6 => 'JUNIO',
            7 => 'JULIO', 8 => 'AGOSTO', 9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE',
        ];

        $fecha = $acta->fecha ? \Carbon\Carbon::parse($acta->fecha) : null;

        return [
            $acta->id,
            $fecha ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($fecha->toDateTime()) : null,
            $fecha ? ($meses[$fecha->month] ?? 'N/A') : 'N/A',
            $acta->establecimiento->codigo ?? 'N/A',
            $acta->establecimiento->nombre ?? 'N/A',
            $acta->establecimiento->provincia ?? 'N/A',
            $acta->establecimiento->distrito ?? 'N/A',
            $acta->responsable ?? 'N/A',
            $acta->tema ?? 'N/A',
            $acta->modalidad ?? 'N/A',
            $acta->implementador ?? 'N/A',
            $acta->participantes->pluck('modulo')->filter()->unique()->implode(', ') ?: 'N/A',
            $acta->participantes->count(),
            $acta->firmado ? 'FIRMADO' : 'PENDIENTE',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'],  // emerald-600
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // N°
            'B' => 12,  // Fecha
            'C' => 14,  // Mes
            'D' => 12,  // IPRESS
            'E' => 38,  // Establecimiento
            'F' => 15,  // Provincia
            'G' => 15,  // Distrito
            'H' => 28,  // Responsable
            'I' => 35,  // Tema / Motivo
            'J' => 18,  // Modalidad
            'K' => 28,  // Implementador
            'L' => 40,  // Módulos Atendidos
            'M' => 14,  // N° Participantes
            'N' => 12,  // Estado
        ];
    }
}
