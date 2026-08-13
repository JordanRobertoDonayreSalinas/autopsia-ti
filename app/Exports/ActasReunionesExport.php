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
use Carbon\Carbon;

class ActasReunionesExport implements
    FromCollection, WithHeadings, WithMapping,
    WithStyles, WithColumnWidths, WithColumnFormatting
{
    protected $reuniones;

    public function __construct($reuniones)
    {
        $this->reuniones = $reuniones;
    }

    public function collection()
    {
        return $this->reuniones;
    }

    public function headings(): array
    {
        return [
            'N° Acta',
            'Fecha',
            'Mes',
            'Hora Inicio',
            'Hora Fin',
            'Título de la Reunión',
            'Institución / Establecimiento',
            'Descripción General',
            'Firmado',
            'Anulado',
        ];
    }

    public function map($item): array
    {
        $meses = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO',
            4 => 'ABRIL', 5 => 'MAYO', 6 => 'JUNIO',
            7 => 'JULIO', 8 => 'AGOSTO', 9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE',
        ];

        $fecha = $item->fecha_reunion ? Carbon::parse($item->fecha_reunion) : null;
        
        return [
            $item->id,
            $fecha ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($fecha->toDateTime()) : null,
            $fecha ? ($meses[$fecha->month] ?? 'N/A') : 'N/A',
            $item->hora_reunion,
            $item->hora_finalizada_reunion ?? 'N/A',
            $item->titulo_reunion,
            $item->nombre_institucion,
            $item->descripcion_general,
            $item->firmado ? 'FIRMADO' : 'PENDIENTE',
            $item->anulado ? 'SÍ' : 'NO',
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
                    'startColor' => ['rgb' => '4F46E5'], // indigo-600
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
            'A' => 10,  // N° Acta
            'B' => 12,  // Fecha
            'C' => 14,  // Mes
            'D' => 12,  // Hora Inicio
            'E' => 12,  // Hora Fin
            'F' => 40,  // Título
            'G' => 30,  // Institución
            'H' => 50,  // Descripción
            'I' => 15,  // Firmado
            'J' => 10,  // Anulado
        ];
    }
}
