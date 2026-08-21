<?php

namespace App\Exports;

use App\Helpers\ModuloHelper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Exporta el Reporte de Personal de Salud: un renglón por cada trabajador
 * registrado en el módulo RR.HH de cada acta. Recibe filas ya armadas por
 * ReportePersonalSaludController::construirFilas().
 */
class PersonalSaludExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithColumnFormatting
{
    protected $filas;

    public function __construct($filas)
    {
        $this->filas = $filas;
    }

    public function collection()
    {
        return $this->filas;
    }

    public function headings(): array
    {
        return [
            'N° Acta',
            'Fecha',
            'IPRESS',
            'Establecimiento',
            'Tipo',
            'Provincia',
            'Distrito',
            'Servicio',
            'Tipo Doc.',
            'N° Documento',
            'Apellido Paterno',
            'Apellido Materno',
            'Nombres',
            'Profesión',
            'Colegio Profesional',
            'N° Colegiatura',
            'RNE',
            'Correo',
            'Celular',
            'Tiene DNIe',
            'Versión DNIe',
            'Es SERUMS',
            'Periodo SERUMS',
        ];
    }

    public function map($fila): array
    {
        $est = $fila['establecimiento'];
        $t = $fila['trabajador'];
        $fecha = $fila['fecha'] ? \Carbon\Carbon::parse($fila['fecha']) : null;

        return [
            $fila['numeroActa'],
            $fecha ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($fecha->toDateTime()) : null,
            $est->codigo ?? 'N/A',
            $est->nombre ?? 'N/A',
            ModuloHelper::getTipoEstablecimiento($est),
            $est->provincia ?? 'N/A',
            $est->distrito ?? 'N/A',
            $t['servicio'] ?? '',
            $t['tipo_doc'] ?? '',
            $t['doc'] ?? '',
            $t['apellido_paterno'] ?? '',
            $t['apellido_materno'] ?? '',
            $t['nombres'] ?? '',
            $t['profesion'] ?? '',
            $t['colegio_profesional'] ?? '',
            $t['colegiatura'] ?? '',
            $t['rne'] ?? '',
            $t['correo'] ?? '',
            $t['celular'] ?? '',
            $t['tiene_dnie'] ?? 'NO',
            $t['version_dnie'] ?? '',
            $t['es_serums'] ?? 'NO',
            $t['periodo_serums'] ?? '',
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
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0EA5E9']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, 'B' => 12, 'C' => 10, 'D' => 32, 'E' => 16, 'F' => 14,
            'G' => 14, 'H' => 20, 'I' => 10, 'J' => 14, 'K' => 20, 'L' => 20,
            'M' => 24, 'N' => 26, 'O' => 18, 'P' => 14, 'Q' => 12, 'R' => 26,
            'S' => 14, 'T' => 12, 'U' => 12, 'V' => 12, 'W' => 14,
        ];
    }
}
