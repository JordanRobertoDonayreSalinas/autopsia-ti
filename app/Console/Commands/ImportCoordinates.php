<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportCoordinates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:coordinates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa coordenadas (lat/lon) desde storage/establecimientos.xls a la tabla establecimientos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = storage_path('establecimientos.xls');
        if (!file_exists($file)) {
            $this->error("El archivo $file no existe.");
            return;
        }

        $this->info("Leyendo archivo Excel...");

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $count = 0;
            $updated = 0;

            foreach ($rows as $index => $row) {
                // Skip header
                if ($index === 0)
                    continue;

                $codigo = $row[1] ?? null; // Columna 1: Código Único
                // Columna 26: Latitud (aprox -14)
                // Columna 27: Longitud (aprox -75)
                $lat = $row[26] ?? null;
                $lon = $row[27] ?? null;

                if ($codigo && $lat && $lon) {
                    $establecimiento = \App\Models\Establecimiento::where('codigo', $codigo)->first();
                    if ($establecimiento) {
                        $establecimiento->update([
                            'latitud' => $lat,
                            'longitud' => $lon
                        ]);
                        $updated++;
                    }
                }
                $count++;
                if ($count % 100 === 0) {
                    $this->info("Procesados $count registros...");
                }
            }

            $this->info("Importación completada. Se actualizaron $updated establecimientos.");

        } catch (\Exception $e) {
            $this->error("Error al leer el archivo: " . $e->getMessage());
        }
    }
}
