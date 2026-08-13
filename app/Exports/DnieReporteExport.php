<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DnieReporteExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $datos;

    public function __construct($datos)
    {
        $this->datos = $datos;
    }

    public function title(): string
    {
        return 'Reporte DNI Electrónico';
    }

    public function collection()
    {
        return $this->datos;
    }

    public function headings(): array
    {
        return [
            // Columnas originales del Excel (A–M)
            'IPRESS',
            'Establecimiento',
            'Categoría',
            'Distrito',
            'Provincia',
            'Microred',
            'Red',
            'Profesión',
            'Tipo de Documento',
            'Número de Documento',
            'Apellido Paterno',
            'Apellido Materno',
            'Nombres',
            // Columnas enriquecidas DNIe (N–S)
            '¿Tiene DNI Electrónico?',
            'Versión DNIe',
            '¿Certificado Digital Activo?',
            'Vigencia del Certificado',
            'Fuente de Versión',
            'Estado Consulta',
        ];
    }

    public function map($fila): array
    {
        // $fila es un array asociativo
        return [
            $fila['ipress']           ?? '',
            $fila['nombre_estab']     ?? '',
            $fila['categoria']        ?? '',
            $fila['distrito']         ?? '',
            $fila['provincia']        ?? '',
            $fila['microred']         ?? '',
            $fila['red']              ?? '',
            $fila['profesion']        ?? '',
            $fila['tipo_documento']   ?? '',
            $fila['num_documento']    ? ' ' . $fila['num_documento'] : '',
            $fila['ap_paterno']       ?? '',
            $fila['ap_materno']       ?? '',
            $fila['nombres']          ?? '',
            $fila['tiene_dnie']       ?? '-',
            $fila['version_dnie']     ?? '-',
            $fila['cert_vigente']     ?? '-',
            $fila['fecha_expiracion'] ?? '-',
            $fila['version_fuente']   ?? '-',
            $fila['estado_consulta']  ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        // ── Estilo fila de encabezado ───────────────────────────────────────
        // Columnas originales A–M → verde oscuro
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 10,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1A7A4A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);

        // Columnas DNIe N–S → azul RENIEC
        $sheet->getStyle('N1:S1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 10,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1A3A7A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
        ]);

        // ── Colorear filas de datos según estado ────────────────────────────
        if ($lastRow > 1) {
            for ($i = 2; $i <= $lastRow; $i++) {
                $estado = $sheet->getCell('S' . $i)->getValue();

                $bgColor = match(true) {
                    $estado === 'DNIe ACTIVO'                 => 'D4EDDA', // verde claro
                    $estado === 'Certificado digital vencido' => 'FFF3CD', // amarillo
                    $estado === 'SIN DNIe'                    => 'F8D7DA', // rojo claro
                    $estado === 'NO APLICA'                   => 'E2E3E5', // gris claro
                    str_contains((string)$estado, 'ERROR')    => 'FFE5B4', // naranja
                    default                                   => 'FFFFFF',
                };

                $sheet->getStyle("A{$i}:S{$i}")->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $bgColor],
                    ],
                    'font' => [
                        'size' => 9,
                    ],
                ]);
            }
        }

        // ── Bordes generales ────────────────────────────────────────────────
        if ($lastRow > 0) {
            $sheet->getStyle("A1:S{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => 'CCCCCC'],
                    ],
                ],
            ]);
        }

        // ── Altura de filas ─────────────────────────────────────────────────
        $sheet->getRowDimension(1)->setRowHeight(35);

        // ── Columna J (Número de Documento) → formato TEXTO para preservar ceros ──
        // Esto evita que Excel interprete "07868057" como el número 7868057.
        $sheet->getStyle('J2:J' . ($lastRow ?: 2))->getNumberFormat()
              ->setFormatCode(NumberFormat::FORMAT_TEXT);

        return []; // ya aplicamos estilos directamente
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,  // IPRESS
            'B' => 32,  // Establecimiento
            'C' => 8,   // Categoría
            'D' => 16,  // Distrito
            'E' => 16,  // Provincia
            'F' => 20,  // Microred
            'G' => 22,  // Red
            'H' => 28,  // Profesión
            'I' => 18,  // Tipo Documento
            'J' => 18,  // Número Documento
            'K' => 22,  // Apellido Paterno
            'L' => 22,  // Apellido Materno
            'M' => 28,  // Nombres
            'N' => 22,  // ¿Tiene DNIe?
            'O' => 16,  // Versión DNIe
            'P' => 26,  // ¿Certificado Activo?
            'Q' => 26,  // Vigencia Certificado
            'R' => 22,  // Fuente Versión
            'S' => 22,  // Estado Consulta
        ];
    }
}
