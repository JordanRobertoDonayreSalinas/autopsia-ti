<?php

namespace App\Http\Controllers;

use App\Models\MonitoreoModulos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Permite subir fotos de evidencia de un consultorio directamente desde el
 * celular del auditor, escaneando un código QR generado desde el formulario
 * en la computadora: evita transferir fotos del celular a la laptop y
 * subirlas una por una adivinando a qué consultorio pertenecía cada una.
 *
 * Mismo patrón que FirmaMovilController: token aleatorio guardado en cache
 * (nada de login en el celular), página móvil pública validada solo por ese
 * token, y la computadora sondea el estado para reflejar las fotos nuevas
 * sin recargar la página.
 */
class EvidenciaMovilController extends Controller
{
    private const MAX_EVIDENCIAS = 10;
    private const MINUTOS_VIGENCIA = 240; // 4 horas: duración típica de una visita

    /**
     * (Autenticado, desde la laptop.) Genera el token + QR que abre la
     * página móvil de carga, ya vinculada a este consultorio específico.
     */
    public function generarQr(Request $request, $id, $slug)
    {
        try {
            MonitoreoModulos::where('cabecera_monitoreo_id', $id)
                ->where('modulo_nombre', $slug)
                ->firstOrFail();

            // Si ya había un QR activo para este consultorio, se invalida:
            // solo debe haber uno vigente a la vez (evita códigos viejos
            // sueltos que sigan aceptando fotos después de generar uno nuevo).
            $this->cerrarActivo($id, $slug);

            $token = Str::random(40);

            Cache::put("evidencia_movil_{$token}", [
                'cabecera_monitoreo_id' => $id,
                'slug' => $slug,
            ], now()->addMinutes(self::MINUTOS_VIGENCIA));
            Cache::put($this->claveActiva($id, $slug), $token, now()->addMinutes(self::MINUTOS_VIGENCIA));

            $url = route('evidencia.movil.mostrar', ['token' => $token]);
            // Formato SVG explícito: liviano y no depende de Imagick/GD,
            // que puede no estar disponible en el servidor.
            $qrImage = QrCode::format('svg')->size(220)->color(30, 41, 59)->generate($url);

            return response()->json([
                'token' => $token,
                'url' => $url,
                'qr_html' => (string) $qrImage,
            ]);
        } catch (\Throwable $e) {
            Log::error("Error al generar QR de evidencia móvil (acta {$id}, módulo {$slug}): " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'No se pudo generar el código QR: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * (Autenticado — se llama internamente al guardar el formulario del
     * consultorio.) Cierra/invalida el código QR activo de este consultorio,
     * si lo hay, para que no quede sobrecargando el sistema con sondeos ni
     * aceptando fotos nuevas después de guardada la evaluación.
     */
    public function cerrarActivo($id, $slug): void
    {
        $token = Cache::pull($this->claveActiva($id, $slug));
        if ($token) {
            Cache::forget("evidencia_movil_{$token}");
        }
    }

    private function claveActiva($id, $slug): string
    {
        return "evidencia_movil_activo_{$id}_{$slug}";
    }

    /**
     * (Público, sin login — accedido por el celular al escanear el QR.)
     * Página móvil: cámara + descripción + subir.
     */
    public function mostrar($token)
    {
        $datos = Cache::get("evidencia_movil_{$token}");
        abort_if(!$datos, 410, 'Este código QR ya expiró. Genera uno nuevo desde la computadora.');

        $detalle = MonitoreoModulos::where('cabecera_monitoreo_id', $datos['cabecera_monitoreo_id'])
            ->where('modulo_nombre', $datos['slug'])
            ->firstOrFail();

        $contenido = $detalle->contenido ?? [];
        $tituloConsultorio = $contenido['titulo_consultorio'] ?? 'Consultorio';
        $totalActual = count($contenido['evidencias'] ?? []);

        return view('wizard.evidencia-movil', [
            'token' => $token,
            'tituloConsultorio' => $tituloConsultorio,
            'totalActual' => $totalActual,
            'maxEvidencias' => self::MAX_EVIDENCIAS,
        ]);
    }

    /**
     * (Público, sin login.) Sube una foto + descripción y la adjunta de
     * inmediato al consultorio: no espera a que se guarde el formulario
     * completo en la laptop, la foto queda a salvo apenas se toma.
     */
    public function subir(Request $request, $token)
    {
        $datos = Cache::get("evidencia_movil_{$token}");
        if (!$datos) {
            return response()->json(['success' => false, 'message' => 'Este código QR ya expiró. Pide uno nuevo desde la computadora.'], 410);
        }

        $request->validate([
            'foto' => 'required|image|max:10240',
            'descripcion' => 'required|string|max:255',
        ]);

        $id = $datos['cabecera_monitoreo_id'];
        $slug = $datos['slug'];

        $detalle = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', $slug)
            ->firstOrFail();

        $contenido = $detalle->contenido ?? [];
        $evidencias = $contenido['evidencias'] ?? [];

        if (count($evidencias) >= self::MAX_EVIDENCIAS) {
            return response()->json(['success' => false, 'message' => 'Ya se alcanzó el máximo de ' . self::MAX_EVIDENCIAS . ' fotos para este consultorio.'], 422);
        }

        $slugLimpio = Str::slug($slug, '_');
        $numFoto = count($evidencias) + 1;
        $extension = strtolower($request->file('foto')->getClientOriginalExtension() ?: 'jpg');
        $nombreEstandar = "evidencia_acta_{$id}_{$slugLimpio}_{$numFoto}_" . date('Ymd_His') . '_' . uniqid() . '.' . $extension;
        $path = $request->file('foto')->storeAs('evidencias_monitoreo', $nombreEstandar, 'public');

        $evidencias[] = [
            'path' => $path,
            'descripcion' => mb_strtoupper(trim($request->input('descripcion'))),
        ];

        $contenido['evidencias'] = $evidencias;
        $detalle->update(['contenido' => $contenido]);

        return response()->json([
            'success' => true,
            'total' => count($evidencias),
            'restantes' => self::MAX_EVIDENCIAS - count($evidencias),
        ]);
    }

    /**
     * (Autenticado, desde la laptop.) Sondeo: la lista actual de evidencias
     * del consultorio, para que el formulario detecte fotos nuevas subidas
     * desde el celular y las inserte en pantalla sin recargar la página.
     */
    public function estado($id, $slug)
    {
        $detalle = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', $slug)
            ->firstOrFail();

        $contenido = $detalle->contenido ?? [];

        return response()->json([
            'evidencias' => $contenido['evidencias'] ?? [],
        ]);
    }
}
