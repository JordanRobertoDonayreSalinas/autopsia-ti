<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class Ficha42Export implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $data;
    protected $rowIndex = 0;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function title(): string
    {
        return 'ANEXO 1 (CM 42)';
    }

    public function headings(): array
    {
        return [
            ['ANEXO 1: LISTADO DE EESS CON EQUIPAMIENTO Y CONECTIVIDAD IMPLEMENTADA'],
            [],
            [
                'Orden',
                'DISA / DIRESA',
                'Categoría',
                'Código RENIPRESS',
                'Nombre del Establecimiento de Salud (EESS)',
                'Tipo de Equipo',
                'ACTUAL: TRIAJE',
                'ACTUAL: CONSULTORIO',
                'ACTUAL: VENTANILLA UNICA, CAJA Y ADMISION',
                'ACTUAL: PROGRAMACION',
                'ACTUAL: ACCESO RED',
                'ACTUAL: INTERNET',
                'FALTANTE: TRIAJE',
                'FALTANTE: CONSULTORIO',
                'FALTANTE: VENTANILLA UNICA, CAJA Y ADMISION',
                'FALTANTE: PROGRAMACION',
                'FALTANTE: ACCESO RED',
                'FALTANTE: INTERNET',
                'Gestión del Requerimiento'
            ]
        ];
    }

    public function map($row): array
    {
        // Solo incrementamos el orden si hay nombre de EESS (es la primera fila del grupo)
        if (!empty($row['nombre'])) {
            $this->rowIndex++;
            $orden = $this->rowIndex;
            $disa = 'ICA';
        } else {
            $orden = '';
            $disa = '';
        }
        
        return [
            $orden,
            $disa,
            $row['categoria'],
            $row['codigo'],
            $row['nombre'],
            $row['tipo_equipo'],
            $row['triaje'] > 0 ? $row['triaje'] : '',
            $row['consultorio'] > 0 ? $row['consultorio'] : '',
            $row['admision'] > 0 ? $row['admision'] : '',
            $row['programacion'] > 0 ? $row['programacion'] : '',
            $row['red_val'] ?? '',
            $row['internet_val'] ?? '',
            $row['faltante_triaje'] > 0 ? $row['faltante_triaje'] : '',
            $row['faltante_consultorio'] > 0 ? $row['faltante_consultorio'] : '',
            $row['faltante_admision'] > 0 ? $row['faltante_admision'] : '',
            $row['faltante_programacion'] > 0 ? $row['faltante_programacion'] : '',
            $row['faltante_red_val'] ?? '',
            $row['faltante_internet_val'] ?? '',
            ''  // Gestión
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->data) + 3;

        // Combinar celdas del título
        $sheet->mergeCells('A1:S1');
        
        // Estilo del título principal
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // --- ESTILOS DE CABECERAS (Fila 3) ---
        
        $headerStyleBlue = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        $sheet->getStyle('A3:F3')->applyFromArray($headerStyleBlue);
        $sheet->getStyle('S3')->applyFromArray($headerStyleBlue);

        // Cabecera ACTUAL (Verde)
        $sheet->getStyle('G3:L3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6E0B4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        // Cabecera FALTANTE (Naranja)
        $sheet->getStyle('M3:R3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F4B084']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);

        $sheet->getRowDimension(3)->setRowHeight(45);

        // --- ESTILOS DE DATOS DINÁMICOS (Fila 4 en adelante) ---
        
        $fillActual   = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']]]; // Verde claro
        $fillFaltante = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']]]; // Naranja claro

        for ($i = 4; $i <= $lastRow; $i++) {
            $tipo = $sheet->getCell('F' . $i)->getValue();
            
            // Bordes y alineación básica para toda la fila
            $sheet->getStyle('A'.$i.':S'.$i)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('G'.$i.':S'.$i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Mapeo de columnas según el tipo (Basado en análisis de BASE.xlsx)
            if (str_contains($tipo, '01. PC')) {
                $sheet->getStyle('G'.$i.':L'.$i)->applyFromArray($fillActual);
                $sheet->getStyle('M'.$i.':R'.$i)->applyFromArray($fillFaltante);
            } 
            elseif (str_contains($tipo, '02. IMPRESORA')) {
                $sheet->getStyle('H'.$i.':J'.$i)->applyFromArray($fillActual);
                $sheet->getStyle('N'.$i.':P'.$i)->applyFromArray($fillFaltante);
            }
            elseif (str_contains($tipo, '03. IMPRESORA TIKETERA')) {
                $sheet->getStyle('I'.$i)->applyFromArray($fillActual);
                $sheet->getStyle('O'.$i)->applyFromArray($fillFaltante);
            }
            elseif (str_contains($tipo, '04. LECTORA DE DNI')) {
                $sheet->getStyle('H'.$i)->applyFromArray($fillActual);
                $sheet->getStyle('N'.$i)->applyFromArray($fillFaltante);
            }
            elseif (str_contains($tipo, '08. CABLEADO')) {
                $sheet->getStyle('K'.$i)->applyFromArray($fillActual);
                $sheet->getStyle('Q'.$i)->applyFromArray($fillFaltante);
            }
            elseif (str_contains($tipo, '09. OPERADOR') || str_contains($tipo, '10. ANCHO') || str_contains($tipo, '11. FIBRA') || str_contains($tipo, '12. COBRE')) {
                $sheet->getStyle('L'.$i)->applyFromArray($fillActual);
                $sheet->getStyle('R'.$i)->applyFromArray($fillFaltante);
            }
        }

        $sheet->getStyle('A4:S' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // Orden
            'B' => 15,  // DISA
            'C' => 10,  // Cat
            'D' => 12,  // Código
            'E' => 40,  // Nombre
            'F' => 25,  // Tipo equipo
            'G' => 12,  // Triaje
            'H' => 12,  // Consultorio
            'I' => 12,  // Admisión
            'J' => 12,  // Programacion
            'K' => 10,  // Red
            'L' => 10,  // Internet
            'M' => 12,  // F. Triaje
            'N' => 12,  // F. Cons
            'O' => 12,  // F. Adm
            'P' => 12,  // F. Prog
            'Q' => 10,  // F. Red
            'R' => 10,  // F. Int
            'S' => 30,  // Gestión
        ];
    }
}
