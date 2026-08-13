<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MonitoreoModulos;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FirmasMonitoreoController extends Controller
{
    /**
     * Procesa la subida del PDF firmado para cualquier módulo.
     */
    public function subir(Request $request, $id)
    {
        $request->validate([
            'modulo' => 'required|string',
            'pdf_firmado' => 'required|file|mimes:pdf|max:10240', // 10MB
        ]);

        try {
            DB::beginTransaction();

            $modulo = $request->modulo;
            $nombreLimpio = str_replace('_', ' ', $modulo);

            $registro = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
                ->where('modulo_nombre', $modulo)
                ->first();

            if (!$registro) {
                return back()->with('error', "No se puede subir firma: El módulo {$nombreLimpio} no ha sido guardado aún.");
            }

            if ($request->hasFile('pdf_firmado')) {
                // Eliminar archivo anterior si existe
                if ($registro->pdf_firmado_path && Storage::disk('public')->exists($registro->pdf_firmado_path)) {
                    Storage::disk('public')->delete($registro->pdf_firmado_path);
                }

                // Guardar nuevo archivo con nombre único (hash seguro)
                // Usamos uniqid() con more_entropy para evitar colisiones y caché de cPanel
                $filename = "{$modulo}_" . uniqid('', true) . "_" . time() . ".pdf";
                $path = $request->file('pdf_firmado')->storeAs(
                    "firmas/acta_{$id}",
                    $filename,
                    'public'
                );

                $registro->update([
                    'pdf_firmado_path' => $path
                ]);
            }

            DB::commit();
            return back()->with('success', "¡Éxito! El PDF firmado de " . strtoupper($nombreLimpio) . " se cargó correctamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error subiendo firma Módulo {$modulo} en Acta {$id}: " . $e->getMessage());
            return back()->with('error', "Error técnico al subir el archivo.");
        }
    }

    /**
     * Abre el PDF en el navegador para su visualización.
     * Incluye encabezados especiales para evitar caché en cPanel/nginx
     */
    public function ver($id, $modulo)
    {
        $registro = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', $modulo)
            ->firstOrFail();

        if (!$registro->pdf_firmado_path || !Storage::disk('public')->exists($registro->pdf_firmado_path)) {
            return back()->with('error', 'El archivo firmado no existe físicamente en el servidor.');
        }

        $filePath = storage_path('app/public/' . $registro->pdf_firmado_path);
        $fileName = basename($registro->pdf_firmado_path);

        // Retorna el archivo con encabezados que previenen el caché del navegador Y de cPanel
        // Incluye Last-Modified y ETag basados en el archivo real para forzar recarga
        return response()->file($filePath, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0, private',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'Last-Modified' => gmdate('D, d M Y H:i:s', filemtime($filePath)) . ' GMT',
            'ETag' => '"' . md5_file($filePath) . '"',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'X-Accel-Expires' => '0',
            'Vary' => '*'
        ]);
    }

    /**
     * Descarga el PDF firmado.
     */
    public function descargar($id, $modulo)
    {
        $registro = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', $modulo)
            ->firstOrFail();

        if (!$registro->pdf_firmado_path) {
            return back()->with('error', 'El archivo no existe.');
        }

        return Storage::disk('public')->download($registro->pdf_firmado_path);
    }
}