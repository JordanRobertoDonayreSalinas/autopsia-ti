<?php

namespace App\Http\Controllers;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

class FirmaMovilController extends Controller
{

    public function generateQrCode($token)
    {
        // Generamos la URL firmada que vence en 1 MINUTO desde AHORA
        $urlFirmada = URL::temporarySignedRoute(
            'firma.movil',
            now()->addMinute(),
            ['token' => $token]
        );

        // Generamos el gráfico QR
        // Usamos 'svg' porque es ligero y nítido
        $qrImage = QrCode::size(140)->color(30, 41, 59)->generate($urlFirmada);

        return response()->json([
            'qr_html' => (string) $qrImage
        ]);
    }

    public function viewMobilePad($token)
    {
        return view('wizard.mobile-signature', compact('token'));
    }

    public function saveMobileSignature(Request $request, $token)
    {
        // Guardamos en archivo (file cache) por 10 minutos
        Cache::put('firma_temp_' . $token, $request->firma, 600);
        return response()->json(['success' => true]);
    }

    public function checkSignatureStatus($token)
    {
        $firma = Cache::get('firma_temp_' . $token);
        if ($firma) {
            return response()->json(['status' => 'signed', 'signature' => $firma]);
        }
        return response()->json(['status' => 'waiting']);
    }
}
