<?php

namespace App\Exports;

use App\Models\EquipoComputo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EquiposExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithColumnFormatting
{
    protected $equipos;

    public function __construct($equipos)
    {
        $this->equipos = $equipos;
    }

    /**
     * Retorna la colección de equipos a exportar
     */
    public function collection()
    {
        return $this->equipos;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Mes',
            'IPRESS',
            'Establecimiento',
            'Categoría',
            'Tipo',
            'Módulo',
            'Servicio',
            'Departamento',
            'Consultorio',
            'Vinculado a',
            'Cantidad',
            'Descripción',
            'Modelo (Especif.)',
            'Procesador (Especif.)',
            'RAM (Especif.)',
            'Disco (Especif.)',
            'GPU (Especif.)',
            'S.O. (Especif.)',
            'Origen Especif.',
            'Propio',
            'N° Serie',
            'Observación',
            'Estado',
            'Provincia',
            'Distrito',
            'Conectividad',
            'Fuente WiFi',
            'Proveedor',
        ];
    }

    public function map($equipo): array
    {
        $meses = [
            1 => 'ENERO',
            2 => 'FEBRERO',
            3 => 'MARZO',
            4 => 'ABRIL',
            5 => 'MAYO',
            6 => 'JUNIO',
            7 => 'JULIO',
            8 => 'AGOSTO',
            9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE',
            11 => 'NOVIEMBRE',
            12 => 'DICIEMBRE',
        ];

        $fecha = $equipo->cabecera->fecha ? \Carbon\Carbon::parse($equipo->cabecera->fecha) : null;
        $datosConsultorio = \App\Helpers\ModuloHelper::getDatosConsultorio($equipo->cabecera, $equipo->modulo);
        $specs = is_array($equipo->especificaciones) ? $equipo->especificaciones : [];

        return [
            $fecha ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($fecha->toDateTime()) : null,
            $fecha ? ($meses[$fecha->month] ?? 'N/A') : 'N/A',
            $equipo->cabecera->establecimiento->codigo ?? 'N/A',
            $equipo->cabecera->establecimiento->nombre ?? 'N/A',
            $equipo->cabecera->establecimiento->categoria ?? 'N/A',
            \App\Helpers\ModuloHelper::getTipoEstablecimiento($equipo->cabecera->establecimiento),
            \App\Helpers\ModuloHelper::getNombreModulo($equipo->cabecera, $equipo->modulo),
            $datosConsultorio['servicio_asociado'] ?: 'N/A',
            $datosConsultorio['departamento_asociado'] ?: 'N/A',
            $datosConsultorio['tipo_consultorio'] ?: 'N/A',
            $datosConsultorio['vinculado_a'] ?: '',
            $equipo->cantidad ?? 0,
            $equipo->descripcion ?? 'N/A',
            $specs['modelo'] ?? '',
            $specs['procesador'] ?? '',
            $specs['ram'] ?? '',
            $specs['disco'] ?? '',
            $specs['gpu'] ?? '',
            $specs['so'] ?? '',
            empty($specs) ? '' : (!empty($specs['autodetectado']) ? 'AUTODETECTADO' : 'MANUAL'),
            $equipo->propio ?? 'N/A',
            $equipo->nro_serie ?: '',
            $equipo->observacion ?: '',
            $equipo->estado ?? 'N/A',
            $equipo->cabecera->establecimiento->provincia ?? 'N/A',
            $equipo->cabecera->establecimiento->distrito ?? 'N/A',
            ($conectividad = \App\Helpers\ModuloHelper::getConectividadActa($equipo->cabecera, $equipo->modulo))['tipo'],
            $conectividad['fuente'],
            $conectividad['operador'],
        ];
    }

    /**
     * Formato de columnas (fecha nativa en Excel)
     */
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    /**
     * Aplica estilos a la hoja de Excel
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la fila de encabezados
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '6366F1'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // Fecha
            'B' => 14,  // Mes
            'C' => 12,  // IPRESS
            'D' => 35,  // Establecimiento
            'E' => 12,  // Categoría
            'F' => 18,  // Tipo
            'G' => 30,  // Módulo
            'H' => 22,  // Servicio
            'I' => 25,  // Departamento
            'J' => 12,  // Consultorio (FISICO/FUNCIONAL)
            'K' => 22,  // Vinculado a
            'L' => 10,  // Cantidad
            'M' => 30,  // Descripción
            'N' => 24,  // Modelo (Especif.)
            'O' => 30,  // Procesador (Especif.)
            'P' => 14,  // RAM (Especif.)
            'Q' => 26,  // Disco (Especif.)
            'R' => 26,  // GPU (Especif.)
            'S' => 20,  // S.O. (Especif.)
            'T' => 16,  // Origen Especif.
            'U' => 12,  // Propio
            'V' => 16,  // N° Serie
            'W' => 25,  // Observación
            'X' => 12,  // Estado
            'Y' => 15,  // Provincia
            'Z' => 15,  // Distrito
            'AA' => 18, // Conectividad
            'AB' => 18, // Fuente WiFi
            'AC' => 18, // Proveedor
        ];
    }
}
