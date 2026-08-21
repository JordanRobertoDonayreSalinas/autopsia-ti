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

class ActasMonitoreoExport implements
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

    /**
     * Orden canónico de módulos (igual que $modulosMaster en ModuloHelper)
     */
    protected function moduloOrden(): array
    {
        return [
            'gestion_administrativa', 'citas', 'triaje',
            'consulta_medicina', 'consulta_odontologia', 'consulta_nutricion', 'consulta_psicologia',
            'cred', 'inmunizaciones', 'atencion_prenatal', 'planificacion_familiar',
            'parto', 'puerperio', 'fua_electronico', 'farmacia', 'referencias', 'laboratorio', 'urgencias',
            // Especializados
            'gestion_admin_esp', 'citas_esp', 'triaje_esp', 'salud_mental_group', 'toma_muestra', 'farmacia_esp',
            // Sub-módulos Salud Mental
            'sm_medicina_general', 'sm_psiquiatria', 'sm_med_familiar',
            'sm_psicologia', 'sm_enfermeria', 'sm_servicio_social', 'sm_terapias',
        ];
    }

    public function headings(): array
    {
        return [
            'N° Acta',
            'Fecha',
            'Mes',
            'Tipo',
            'IPRESS',
            'Establecimiento',
            'Categoría',
            'Provincia',
            'Distrito',
            'Responsable',
            'Implementador',
            'Módulos Monitoreados',
            'Progreso (%)',
            'Pozo a Tierra',
            'Pozo Cant.',
            'Pozo Operativos',
            'Pozo Inoperativos',
            'Panel Solar',
            'Panel Cant.',
            'Panel Operativos',
            'Panel Inoperativos',
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

        // Módulos registrados ordenados según $modulosMaster
        $modulosRegistrados = $acta->detalles
            ->where('modulo_nombre', '!=', 'config_modulos')
            ->pluck('modulo_nombre')
            ->toArray();

        $orden = $this->moduloOrden();

        $modulosOrdenados = collect($orden)
            ->filter(fn($key) => in_array($key, $modulosRegistrados))
            ->map(fn($key) => \App\Helpers\ModuloHelper::getNombreAmigable($key) ?? $key)
            ->values()
            ->implode(', ');

        $modulosMonitoreados = $modulosOrdenados ?: 'Sin módulos';

        return [
            $acta->numero_acta ?? $acta->id,
            $fecha ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($fecha->toDateTime()) : null,
            $fecha ? ($meses[$fecha->month] ?? 'N/A') : 'N/A',
            $acta->tipo_origen ?? 'N/A',
            $acta->establecimiento->codigo ?? 'N/A',
            $acta->establecimiento->nombre ?? 'N/A',
            $acta->establecimiento->categoria ?? 'N/A',
            $acta->establecimiento->provincia ?? 'N/A',
            $acta->establecimiento->distrito ?? 'N/A',
            $acta->responsable ?? 'N/A',
            $acta->implementador ?? 'N/A',
            $modulosMonitoreados,
            $acta->progreso . '%',
            $acta->pozo_tierra ? 'SI' : 'NO',
            $acta->pozo_tierra_cantidad ?? 0,
            $acta->pozo_tierra_operativos ?? 0,
            $acta->pozo_tierra_inoperativos ?? 0,
            $acta->panel_solar ? 'SI' : 'NO',
            $acta->panel_solar_cantidad ?? 0,
            $acta->panel_solar_operativos ?? 0,
            $acta->panel_solar_inoperativos ?? 0,
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
                    'startColor' => ['rgb' => '3B82F6'], // blue-500
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
            'D' => 16,  // Tipo
            'E' => 12,  // IPRESS
            'F' => 38,  // Establecimiento
            'G' => 14,  // Categoría
            'H' => 15,  // Provincia
            'I' => 15,  // Distrito
            'J' => 28,  // Responsable
            'K' => 28,  // Implementador
            'L' => 60,  // Módulos Completados
            'M' => 14,  // Progreso
            'N' => 14,  // Pozo a Tierra
            'O' => 12,  // Pozo Cant.
            'P' => 14,  // Pozo Operativos
            'Q' => 16,  // Pozo Inoperativos
            'R' => 14,  // Panel Solar
            'S' => 12,  // Panel Cant.
            'T' => 14,  // Panel Operativos
            'U' => 16,  // Panel Inoperativos
            'V' => 12,  // Estado
        ];
    }
}
