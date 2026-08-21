<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Comprime y convierte a WebP las fotos de evidencia que se suben (hasta 10
 * por consultorio, más RR.HH., portada del acta y actas de reunión): sin
 * esto, las fotos de celular (varios MB cada una, JPEG sin comprimir)
 * acumulan varios GB rápido y sobrecargan el hosting compartido (cPanel).
 *
 * Estas mismas fotos se embeben en los PDF de los reportes (DomPDF):
 * verificado que esta versión de DomPDF sí decodifica WebP correctamente
 * (una prueba anterior con file:// daba un falso negativo por un bug de
 * formato de ruta en Windows, no por WebP en sí).
 *
 * Si por cualquier motivo la conversión no se puede hacer (GD no disponible
 * en el servidor, archivo corrupto, formato no soportado), se guarda el
 * archivo original tal cual, sin recomprimir: nunca se debe bloquear ni
 * degradar una subida de evidencia por esto.
 */
class ImagenHelper
{
    private const CALIDAD = 78;
    private const ANCHO_MAXIMO = 1920;

    /**
     * Guarda la imagen subida recomprimida como WebP (o, si no es posible,
     * tal cual la subieron) y devuelve la ruta relativa final dentro del
     * disco indicado. $nombreBase va SIN extensión: esta función le pone la
     * que corresponda.
     */
    public static function guardarComprimida(UploadedFile $archivo, string $carpeta, string $nombreBase, string $disco = 'public'): string
    {
        if (!extension_loaded('gd') || !function_exists('imagewebp')) {
            return self::guardarOriginal($archivo, $carpeta, $nombreBase, $disco);
        }

        $origen = null;
        $lienzo = null;

        try {
            $contenido = file_get_contents($archivo->getRealPath());
            $origen = $contenido !== false ? @imagecreatefromstring($contenido) : false;

            if (!$origen) {
                return self::guardarOriginal($archivo, $carpeta, $nombreBase, $disco);
            }

            $origen = self::corregirOrientacion($archivo, $origen);
            $lienzo = self::prepararLienzo($origen);
            $origen = null; // prepararLienzo ya liberó $origen

            $tmpPath = tempnam(sys_get_temp_dir(), 'foto_');
            $ok = imagewebp($lienzo, $tmpPath, self::CALIDAD);

            if (!$ok || !file_exists($tmpPath) || filesize($tmpPath) === 0) {
                @unlink($tmpPath);

                return self::guardarOriginal($archivo, $carpeta, $nombreBase, $disco);
            }

            $rutaFinal = trim($carpeta, '/').'/'.$nombreBase.'.webp';
            Storage::disk($disco)->put($rutaFinal, file_get_contents($tmpPath));
            @unlink($tmpPath);

            return $rutaFinal;
        } catch (\Throwable $e) {
            Log::warning('No se pudo recomprimir una imagen, se guardó el archivo original: '.$e->getMessage());

            return self::guardarOriginal($archivo, $carpeta, $nombreBase, $disco);
        } finally {
            if ($origen instanceof \GdImage) {
                imagedestroy($origen);
            }
            if ($lienzo instanceof \GdImage) {
                imagedestroy($lienzo);
            }
        }
    }

    /**
     * Las fotos de celular en portrait suelen venir "acostadas" con una
     * etiqueta EXIF de orientación (el navegador/la app de fotos la rota al
     * mostrarla, pero GD no la lee sola): sin esto, la foto resultante
     * quedaría de lado.
     */
    private static function corregirOrientacion(UploadedFile $archivo, \GdImage $imagen): \GdImage
    {
        if (!function_exists('exif_read_data') || !in_array($archivo->getMimeType(), ['image/jpeg', 'image/pjpeg'], true)) {
            return $imagen;
        }

        try {
            $exif = @exif_read_data($archivo->getRealPath());
            $orientacion = $exif['Orientation'] ?? null;

            $rotada = match ($orientacion) {
                3 => imagerotate($imagen, 180, 0),
                6 => imagerotate($imagen, -90, 0),
                8 => imagerotate($imagen, 90, 0),
                default => null,
            };

            if ($rotada) {
                imagedestroy($imagen);

                return $rotada;
            }
        } catch (\Throwable $e) {
            // Sin dato EXIF legible: se continúa sin rotar, no es crítico.
        }

        return $imagen;
    }

    /**
     * Prepara el lienzo final en un solo paso: conserva transparencia (WebP
     * sí soporta canal alfa, a diferencia de JPEG) y, si el ancho excede el
     * máximo, redimensiona proporcionalmente. Destruye $imagen y devuelve un
     * lienzo nuevo — el llamador no debe volver a liberarla.
     */
    private static function prepararLienzo(\GdImage $imagen): \GdImage
    {
        $anchoOrigen = imagesx($imagen);
        $altoOrigen = imagesy($imagen);

        if ($anchoOrigen <= self::ANCHO_MAXIMO) {
            imagesavealpha($imagen, true);

            return $imagen;
        }

        $anchoFinal = self::ANCHO_MAXIMO;
        $altoFinal = (int) round($altoOrigen * (self::ANCHO_MAXIMO / $anchoOrigen));

        $lienzo = imagecreatetruecolor($anchoFinal, $altoFinal);
        imagealphablending($lienzo, false);
        imagesavealpha($lienzo, true);
        $transparente = imagecolorallocatealpha($lienzo, 0, 0, 0, 127);
        imagefill($lienzo, 0, 0, $transparente);
        imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $anchoFinal, $altoFinal, $anchoOrigen, $altoOrigen);
        imagedestroy($imagen);

        return $lienzo;
    }

    private static function guardarOriginal(UploadedFile $archivo, string $carpeta, string $nombreBase, string $disco): string
    {
        $extension = strtolower($archivo->getClientOriginalExtension() ?: 'jpg');

        return $archivo->storeAs($carpeta, "{$nombreBase}.{$extension}", $disco);
    }
}
