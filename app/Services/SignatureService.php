<?php

namespace App\Services;

use App\Models\Profesional;

class SignatureService
{
    /**
     * Dibuja un escudo del Perú simplificado directamente con GD.
     * Sin dependencias de archivos externos.
     */
    private function drawEscudo($img, $x, $y, $w, $h)
    {
        $white  = imagecolorallocate($img, 255, 255, 255);
        $red    = imagecolorallocate($img, 210, 20,  20);
        $red2   = imagecolorallocate($img, 180, 0,   0);
        $green  = imagecolorallocate($img, 0,   130, 0);
        $brown  = imagecolorallocate($img, 139, 90,  43);
        $yellow = imagecolorallocate($img, 220, 180, 0);
        $gold   = imagecolorallocate($img, 200, 155, 0);
        $black  = imagecolorallocate($img, 0,   0,   0);
        $blue   = imagecolorallocate($img, 0,   60,  160);
        $beige  = imagecolorallocate($img, 230, 200, 150);

        // Escudo base (forma de escudo redondeada abajo)
        // Dividido en 3 cuarteles (2 arriba, 1 abajo)
        $shieldW = $w;
        $shieldH = (int)($h * 0.85);

        // Cuartel superior izquierdo (azul) – Vicuña
        imagefilledrectangle($img, $x, $y, $x + (int)($shieldW/2), $y + (int)($shieldH/2), $blue);
        // Vicuña (animal simplificado - elipse pequeña)
        imagefilledellipse($img, $x + (int)($shieldW*0.18), $y + (int)($shieldH*0.30), (int)($shieldW*0.20), (int)($shieldH*0.22), $beige);
        imagefilledellipse($img, $x + (int)($shieldW*0.18), $y + (int)($shieldH*0.18), (int)($shieldW*0.10), (int)($shieldH*0.14), $beige);

        // Cuartel superior derecho (blanco) – Árbol de la quina
        imagefilledrectangle($img, $x + (int)($shieldW/2), $y, $x + $shieldW, $y + (int)($shieldH/2), $white);
        // Árbol (tronco + copa)
        imagefilledrectangle($img, $x + (int)($shieldW*0.63), $y + (int)($shieldH*0.28), $x + (int)($shieldW*0.70), $y + (int)($shieldH*0.50), $brown);
        imagefilledellipse($img, $x + (int)($shieldW*0.67), $y + (int)($shieldH*0.20), (int)($shieldW*0.22), (int)($shieldH*0.20), $green);

        // Cuartel inferior (rojo) – Cornucopia
        imagefilledrectangle($img, $x, $y + (int)($shieldH/2), $x + $shieldW, $y + $shieldH, $red);
        // Cornucopia (arco de círculo + monedas)
        imagearc($img, $x + (int)($shieldW*0.38), $y + (int)($shieldH*0.78), (int)($shieldW*0.40), (int)($shieldH*0.35), 180, 360, $gold);
        imagefilledellipse($img, $x + (int)($shieldW*0.55), $y + (int)($shieldH*0.65), (int)($shieldW*0.12), (int)($shieldH*0.10), $gold);
        imagefilledellipse($img, $x + (int)($shieldW*0.62), $y + (int)($shieldH*0.72), (int)($shieldW*0.10), (int)($shieldH*0.09), $gold);

        // Borde del escudo (negro)
        imagerectangle($img, $x, $y, $x + $shieldW, $y + $shieldH, $black);
        // Línea horizontal del medio
        imageline($img, $x, $y + (int)($shieldH/2), $x + $shieldW, $y + (int)($shieldH/2), $black);
        // Línea vertical del medio (solo parte superior)
        imageline($img, $x + (int)($shieldW/2), $y, $x + (int)($shieldW/2), $y + (int)($shieldH/2), $black);

        // Arco inferior del escudo
        imagefilledarc($img, $x + (int)($shieldW/2), $y + $shieldH, $shieldW, (int)($h*0.30), 0, 180, $black, IMG_ARC_PIE);

        // Corona de laureles (líneas verdes a los lados) - simplificado
        imagefilledarc($img, $x + (int)($shieldW/2), $y + $shieldH, $shieldW + 8, (int)($h*0.30)+8, 0, 180, $green, IMG_ARC_PIE);
        imagefilledarc($img, $x + (int)($shieldW/2), $y + $shieldH, $shieldW, (int)($h*0.30), 0, 180, $red2, IMG_ARC_PIE);
        imagefilledarc($img, $x + (int)($shieldW/2), $y + $shieldH, $shieldW - 6, (int)($h*0.30)-6, 0, 180, $red, IMG_ARC_PIE);

        // Texto "REPUBLICA DEL PERU" en el tope
        $fontSmall = 'C:\Windows\Fonts\arial.ttf';
        imagettftext($img, 5, 0, $x + 2, $y - 2, $black, $fontSmall, "REPUBLICA DEL PERU");
    }

    /**
     * Genera un sello visual IDÉNTICO al estándar ReFirma.
     * Retorna un string base64 PNG.
     */
    public function generateStamp($profesional)
    {
        $width  = 600;
        $height = 120;

        $img = imagecreatetruecolor($width, $height);

        // Fondo blanco
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $white);

        // Colores principales
        $black = imagecolorallocate($img, 0,   0,   0);
        $red   = imagecolorallocate($img, 210, 0,   0);

        // Fuentes
        $font     = 'C:\Windows\Fonts\arial.ttf';
        $fontBold = 'C:\Windows\Fonts\arialbd.ttf';

        // ── CUADRO ROJO ──────────────────────────────────────────────
        $bx = 5; $by = 5; $bw = 165; $bh = 110;

        // Borde rojo de 3 píxeles
        for ($i = 0; $i < 3; $i++) {
            imagerectangle($img, $bx+$i, $by+$i, $bx+$bw-$i, $by+$bh-$i, $red);
        }

        // ── ESCUDO ───────────────────────────────────────────────────
        // Intentar cargar la imagen real del escudo
        $escudo = null;
        $localPath = public_path('images/escudo_peru_gd.png');
        if (file_exists($localPath) && filesize($localPath) > 5000) {
            $data = @file_get_contents($localPath);
            if ($data) $escudo = @imagecreatefromstring($data);
        }

        if ($escudo) {
            imagecopyresampled($img, $escudo, $bx+4, $by+5, 0, 0, 82, 100, imagesx($escudo), imagesy($escudo));
            imagedestroy($escudo);
        } else {
            // Dibujar escudo simplificado con GD
            $this->drawEscudo($img, $bx+4, $by+5, 82, 100);
        }

        // ── TEXTO "FIRMA DIGITAL" ─────────────────────────────────────
        imagettftext($img, 10, 0, $bx+92, $by+53, $black, $fontBold, "FIRMA");
        imagettftext($img, 10, 0, $bx+92, $by+70, $black, $fontBold, "DIGITAL");

        // ── SECCIÓN DERECHA ──────────────────────────────────────────
        $tx = $bx + $bw + 10;
        $ty = $by + 8;

        $nombre = mb_strtoupper(
            trim($profesional->apellido_paterno . ' '
               . ($profesional->apellido_materno ?? '') . ' '
               . $profesional->nombres),
            'UTF-8'
        );
        $dniStr = "FIR " . $profesional->doc . " hard";

        imagettftext($img, 9,  0, $tx, $ty,      $black, $font,     "Firmado digitalmente por:");
        imagettftext($img, 11, 0, $tx, $ty + 22, $black, $fontBold, $nombre);
        imagettftext($img, 11, 0, $tx, $ty + 42, $black, $fontBold, $dniStr);
        imagettftext($img, 9,  0, $tx, $ty + 62, $black, $font,     "Motivo: Soy el autor del");
        imagettftext($img, 9,  0, $tx, $ty + 76, $black, $font,     "documento");
        imagettftext($img, 9,  0, $tx, $ty + 96, $black, $font,     "Fecha: " . date('d/m/Y H:i:s') . "-0500");

        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($data);
    }
}
