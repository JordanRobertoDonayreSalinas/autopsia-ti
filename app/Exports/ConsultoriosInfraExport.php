<?php

namespace App\Exports;

use App\Helpers\ModuloHelper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exporta el reporte de Consultorios (infraestructura): un renglón por
 * consultorio dinámico, ya con los datos resueltos (servicio/departamento,
 * infraestructura heredada si aplica, conteos y alertas) que arma
 * ReporteConsultoriosController::enriquecerModulo().
 */
class ConsultoriosInfraExport implements FromCollection, WithColumnFormatting, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
    protected $consultorios;

    public function __construct($consultorios)
    {
        $this->consultorios = $consultorios;
    }

    public function collection()
    {
        return $this->consultorios;
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
            'Departamento',
            'Servicio',
            'Consultorio',
            'Tipo Consultorio',
            'Vinculado a',
            'Turno',
            'Piso',
            'Aire Acondicionado',
            'Electricidad',
            'Toma Estabilizada',
            'T.E. Internas',
            'T.E. Externas',
            'Toma Comercial',
            'T.C. Internas',
            'T.C. Externas',
            'Punto de Red',
            'Cant. Puntos de Red',
            '¿Requiere más puntos?',
            'Cant. Adicional Requerida',
            'Conectividad',
            'Fuente WiFi',
            'Proveedor',
            'Vel. Descarga',
            'Vel. Subida',
            'Cant. Equipos de Cómputo',
            'Cant. Requerimientos Pendientes',
            'Observaciones',
        ];
    }

    public function map($c): array
    {
        $modulo = $c['modulo'];
        $cabecera = $c['cabecera'];
        $contenido = $c['contenido'];
        $ce = $c['contenidoEfectivo'];
        $datos = $c['datosConsultorio'];
        $conect = $c['conectividad'];
        $est = $cabecera->establecimiento ?? null;

        $fecha = $cabecera->fecha ? \Carbon\Carbon::parse($cabecera->fecha) : null;

        return [
            $fecha ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($fecha->toDateTime()) : null,
            $est->codigo ?? 'N/A',
            $est->nombre ?? 'N/A',
            ModuloHelper::getTipoEstablecimiento($est),
            $est->provincia ?? 'N/A',
            $est->distrito ?? 'N/A',
            ModuloHelper::esModuloFijo($modulo->modulo_nombre) ? ModuloHelper::getNombreModulo($cabecera, $modulo->modulo_nombre) : '',
            $datos['departamento_asociado'] ?: '',
            $datos['servicio_asociado'] ?: '',
            $contenido['titulo_consultorio'] ?? $modulo->modulo_nombre,
            $datos['tipo_consultorio'] ?: 'FISICO',
            $datos['vinculado_a'] ?: '',
            $contenido['turno'] ?? '',
            $contenido['piso'] ?? '',
            strtoupper($contenido['aire_acondicionado'] ?? 'NO'),
            strtoupper($ce['cuenta_electricidad'] ?? 'SI'),
            strtoupper($ce['tiene_toma_estabilizada'] ?? 'NO'),
            $ce['toma_estabilizada_internas'] ?? '',
            $ce['toma_estabilizada_externas'] ?? '',
            strtoupper($ce['tiene_toma_comercial'] ?? 'NO'),
            $ce['toma_comercial_internas'] ?? '',
            $ce['toma_comercial_externas'] ?? '',
            strtoupper($ce['cuenta_punto_red'] ?? 'SI'),
            $ce['cantidad_puntos_red'] ?? '',
            strtoupper($ce['requiere_mas_puntos_red'] ?? 'NO'),
            $ce['cantidad_puntos_red_requerido'] ?? '',
            $conect['tipo'] ?? 'N/A',
            $conect['fuente'] ?? 'N/A',
            $conect['operador'] ?? 'N/A',
            $ce['velocidad_descarga'] ?? '',
            $ce['velocidad_subida'] ?? '',
            $c['cantidadEquipos'],
            $c['cantidadRequerimientos'],
            $contenido['observaciones'] ?? '',
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
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
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
            'G' => 26, 'H' => 22, 'I' => 20, 'J' => 24, 'K' => 14, 'L' => 20,
            'M' => 10, 'N' => 8, 'O' => 16, 'P' => 14, 'Q' => 16, 'R' => 12,
            'S' => 12, 'T' => 14, 'U' => 12, 'V' => 12, 'W' => 14, 'X' => 16,
            'Y' => 18, 'Z' => 20, 'AA' => 16, 'AB' => 16, 'AC' => 18, 'AD' => 14,
            'AE' => 14, 'AF' => 16, 'AG' => 20, 'AH' => 30,
        ];
    }
}
