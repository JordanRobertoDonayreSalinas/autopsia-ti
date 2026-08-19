<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportSchemaJson extends Command
{
    /**
     * El nombre y firma del comando de consola.
     */
    protected $signature = 'schema:export-json {--output=database_schema.json}';

    /**
     * La descripción del comando de consola.
     */
    protected $description = 'Exporta a JSON el esquema real (tabla, columna, tipo, nulabilidad, default) de las tablas "offline-first", para que Flutter derive su modelo local de Laravel y no al revés.';

    /**
     * Tablas que se capturan en campo sin señal (ver Informe de revisión, Fase 0).
     * Todo lo demás (reportes, auditorías, cronograma, banco de firmas, admin de
     * usuarios, infraestructura 2D colaborativa) se queda 100% en la web.
     */
    private const TABLAS_OFFLINE_FIRST = [
        'establecimientos',
        'mon_cabecera_monitoreo',
        'mon_equipo_monitoreo',
        'mon_monitoreo_modulos',
        'mon_equipos_computo',
        'mon_profesionales',
        'reuniones',
    ];

    public function handle(): int
    {
        $database = DB::connection()->getDatabaseName();
        $schema = [
            'generated_at' => now()->toIso8601String(),
            'database'     => $database,
            'tables'       => [],
        ];

        foreach (self::TABLAS_OFFLINE_FIRST as $table) {
            if (!$this->tableExists($database, $table)) {
                $this->warn("Tabla '{$table}' no existe en la base de datos, se omite.");
                continue;
            }

            $columns = DB::select(
                'SELECT COLUMN_NAME as name, DATA_TYPE as data_type, IS_NULLABLE as nullable,
                        COLUMN_DEFAULT as `default`, COLUMN_KEY as `key`, EXTRA as extra
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                 ORDER BY ORDINAL_POSITION',
                [$database, $table]
            );

            $schema['tables'][$table] = array_map(function ($col) {
                return [
                    'name'     => $col->name,
                    'type'     => $col->data_type,
                    'nullable' => $col->nullable === 'YES',
                    'default'  => $col->default,
                    'primary'  => $col->key === 'PRI',
                    'auto_increment' => str_contains($col->extra ?? '', 'auto_increment'),
                ];
            }, $columns);

            $this->info("✓ {$table} (" . count($columns) . ' columnas)');
        }

        $outputPath = storage_path('app/' . $this->option('output'));
        file_put_contents($outputPath, json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->newLine();
        $this->info("Esquema exportado a: {$outputPath}");

        return self::SUCCESS;
    }

    private function tableExists(string $database, string $table): bool
    {
        $result = DB::selectOne(
            'SELECT COUNT(*) as total FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$database, $table]
        );

        return ($result->total ?? 0) > 0;
    }
}
