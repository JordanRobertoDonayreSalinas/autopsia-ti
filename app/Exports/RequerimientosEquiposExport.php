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
 * Exporta el requerimiento de equipos: un renglón por cada equipo que un
 * consultorio necesita y todavía no tiene (tabla mon_equipos_requerimiento).
 * Recibe filas ya armadas por
 * ReporteConsultoriosController::exportarRequerimientosExcel().
 */
class RequerimientosEquiposExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithColumnFormatting
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
            'Fecha',
            'IPRESS',
            'Establecimiento',
            'Tipo',
            'Provincia',
            'Distrito',
            'Módulo',
            'Consultorio',
            'Servicio',
            'Departamento',
            'Tipo Equipo Requerido',
            'Cantidad',
            'Observación',
        ];
    }

    public function map($fila): array
    {
        $est = $fila['establecimiento'];
        $req = $fila['requerimiento'];
        $datos = $fila['datosConsultorio'];
        $fecha = $fila['fecha'] ? \Carbon\Carbon::parse($fila['fecha']) : null;

        return [
            $fecha ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($fecha->toDateTime()) : null,
            $est->codigo ?? 'N/A',
            $est->nombre ?? 'N/A',
            ModuloHelper::getTipoEstablecimiento($est),
            $est->provincia ?? 'N/A',
            $est->distrito ?? 'N/A',
            $fila['modulo'],
            $fila['titulo_consultorio'],
            $datos['servicio_asociado'] ?: '',
            $datos['departamento_asociado'] ?: '',
            $req->descripcion,
            $req->cantidad ?? 1,
            $req->observacion ?: '',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D97706']],
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
            'A' => 12, 'B' => 10, 'C' => 32, 'D' => 16, 'E' => 14, 'F' => 14,
            'G' => 26, 'H' => 24, 'I' => 20, 'J' => 22, 'K' => 26, 'L' => 10,
            'M' => 40,
        ];
    }
}
