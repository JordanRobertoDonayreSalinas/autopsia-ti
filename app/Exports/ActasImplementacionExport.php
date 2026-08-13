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
use App\Helpers\ImplementacionHelper;

class ActasImplementacionExport implements
    FromCollection, WithHeadings, WithMapping,
    WithStyles, WithColumnWidths, WithColumnFormatting
{
    protected $actas;
    protected $modulosNombres;

    public function __construct($actas)
    {
        $this->actas = $actas;
        
        // Mapear key -> Nombre para mostrar en Excel
        $this->modulosNombres = [];
        foreach (ImplementacionHelper::getModulos() as $key => $config) {
            $this->modulosNombres[$key] = $config['nombre'];
        }
    }

    public function collection()
    {
        return $this->actas;
    }

    public function headings(): array
    {
        return [
            'N° Acta',
            'Módulo',
            'Fecha',
            'Mes',
            'IPRESS',
            'Establecimiento',
            'Categoría',
            'Provincia',
            'Distrito',
            'Responsable',
            'Modalidad',
            'Implementadores',
            'Asistentes (N°)',
            'Estado Doc.',
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
        
        $implementadores = $acta->implementadores->map(function ($imp) {
            return $imp->apellido_paterno . ' ' . $imp->apellido_materno . ', ' . $imp->nombres;
        })->implode(' | ');

        $nAsistentes = $acta->usuarios->count();

        $nombreModulo = $this->modulosNombres[$acta->tipo_key] ?? $acta->tipo_key;

        return [
            $acta->id,
            strtoupper($nombreModulo),
            $fecha ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($fecha->toDateTime()) : null,
            $fecha ? ($meses[$fecha->month] ?? 'N/A') : 'N/A',
            $acta->codigo_establecimiento ?? 'N/A',
            $acta->nombre_establecimiento ?? 'N/A',
            $acta->categoria ?? 'N/A',
            $acta->provincia ?? 'N/A',
            $acta->distrito ?? 'N/A',
            $acta->responsable ?? 'N/A',
            $acta->modalidad ?? 'N/A',
            $implementadores ?: 'Sin asignar',
            $nAsistentes,
            $acta->archivo_pdf ? 'FIRMADA' : 'PENDIENTE',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
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
                    'startColor' => ['rgb' => '8B5CF6'], // purple-500
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
            'B' => 20,  // Módulo
            'C' => 12,  // Fecha
            'D' => 14,  // Mes
            'E' => 12,  // IPRESS
            'F' => 38,  // Establecimiento
            'G' => 14,  // Categoría
            'H' => 15,  // Provincia
            'I' => 15,  // Distrito
            'J' => 28,  // Responsable
            'K' => 20,  // Modalidad
            'L' => 45,  // Implementadores
            'M' => 14,  // Asistentes
            'N' => 15,  // Estado Doc.
        ];
    }
}
