<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditoriaEquiposExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = collect($data);
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'ID ACTA',
            'N° ACTA',
            'FECHA',
            'IPRESS',
            'PROVINCIA',
            'DISTRITO',
            'MÓDULO',
            'CANT. EQUIPOS',
            'CONECTIVIDAD',
            'IMPLEMENTADOR',
            'TIPO INCONSISTENCIA',
        ];
    }

    public function map($item): array
    {
        return [
            $item['acta_id'],
            $item['numero_acta'],
            $item['fecha'] ? \Carbon\Carbon::parse($item['fecha'])->format('d/m/Y') : 'N/A',
            $item['ipress'],
            $item['provincia'],
            $item['distrito'],
            $item['modulo_nombre'],
            $item['equipos_count'],
            $item['conectividad'],
            $item['implementador'],
            $item['tipo_inconsistencia'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '6366F1'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 12,
            'C' => 12,
            'D' => 35,
            'E' => 15,
            'F' => 15,
            'G' => 25,
            'H' => 15,
            'I' => 20,
            'J' => 25,
            'K' => 30,
        ];
    }
}
