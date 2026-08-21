<?php

namespace App\Http\Controllers;

use App\Models\MonitoreoModulos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
            Cache::forget($this->clavePendientes($token));
        }
    }

    private function claveActiva($id, $slug): string
    {
        return "evidencia_movil_activo_{$id}_{$slug}";
    }

    private function clavePendientes(string $token): string
    {
        return "evidencia_movil_pendientes_{$token}";
    }

    /** Fotos realmente cargadas (con path): excluye los espacios de plantilla vacíos. */
    private function fotosReales(array $evidencias): array
    {
        return array_values(array_filter($evidencias, fn ($e) => !empty($e['path'] ?? null)));
    }

    /**
     * Espacios de plantilla (descripción ya puesta, sin foto todavía) que
     * aún no están cubiertos ni por una foto ya guardada ni por una
     * pendiente subida desde este mismo celular, en el orden original.
     */
    private function plantillasPendientes(array $evidencias, array $pendientes): array
    {
        $descripcionesPendientes = array_map(
            fn ($p) => mb_strtoupper(trim($p['descripcion'] ?? '')),
            $pendientes
        );

        return array_values(array_filter($evidencias, function ($e) use ($descripcionesPendientes) {
            if (!empty($e['path'] ?? null)) {
                return false;
            }
            $descripcion = mb_strtoupper(trim($e['descripcion'] ?? ''));

            return $descripcion !== '' && !in_array($descripcion, $descripcionesPendientes, true);
        }));
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
        $evidencias = $contenido['evidencias'] ?? [];
        $pendientes = Cache::get($this->clavePendientes($token), []);
        $plantillas = $this->plantillasPendientes($evidencias, $pendientes);

        return view('wizard.evidencia-movil', [
            'token' => $token,
            'tituloConsultorio' => $tituloConsultorio,
            'evidenciasGuardadas' => $this->fotosReales($evidencias),
            'evidenciasPendientes' => $pendientes,
            'plantillasPendientes' => $plantillas,
            'proximaEtiqueta' => $plantillas[0]['descripcion'] ?? null,
            'maxEvidencias' => self::MAX_EVIDENCIAS,
        ]);
    }

    /**
     * (Público, sin login.) Sube una foto + descripción. El archivo se
     * guarda ya mismo en disco (para no perderlo), pero queda como
     * "pendiente" hasta que el auditor guarde el formulario completo en la
     * laptop: recién ahí se adjunta de verdad al consultorio.
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

        $evidenciasGuardadas = $this->fotosReales($detalle->contenido['evidencias'] ?? []);
        $pendientes = Cache::get($this->clavePendientes($token), []);
        $totalActual = count($evidenciasGuardadas) + count($pendientes);

        if ($totalActual >= self::MAX_EVIDENCIAS) {
            return response()->json(['success' => false, 'message' => 'Ya se alcanzó el máximo de ' . self::MAX_EVIDENCIAS . ' fotos para este consultorio.'], 422);
        }

        $slugLimpio = Str::slug($slug, '_');
        $numFoto = $totalActual + 1;
        $nombreBase = "evidencia_acta_{$id}_{$slugLimpio}_{$numFoto}_" . date('Ymd_His') . '_' . uniqid();
        $path = \App\Helpers\ImagenHelper::guardarComprimida($request->file('foto'), 'evidencias_monitoreo', $nombreBase, 'public');

        $pendientes[] = [
            'path' => $path,
            'descripcion' => mb_strtoupper(trim($request->input('descripcion'))),
        ];
        Cache::put($this->clavePendientes($token), $pendientes, now()->addMinutes(self::MINUTOS_VIGENCIA));

        $totalNuevo = count($evidenciasGuardadas) + count($pendientes);

        return response()->json([
            'success' => true,
            'path' => $path,
            'total' => $totalNuevo,
            'restantes' => self::MAX_EVIDENCIAS - $totalNuevo,
        ]);
    }

    /**
     * (Público, sin login.) Quita una foto pendiente (todavía no guardada
     * en el formulario) directamente desde el celular. Las fotos ya
     * guardadas en el consultorio no se pueden borrar desde aquí: eso se
     * hace desde la laptop y también requiere guardar el formulario.
     */
    public function eliminar(Request $request, $token)
    {
        $datos = Cache::get("evidencia_movil_{$token}");
        if (!$datos) {
            return response()->json(['success' => false, 'message' => 'Este código QR ya expiró.'], 410);
        }

        $request->validate([
            'path' => 'required|string',
        ]);

        $pathABorrar = $request->input('path');
        $pendientes = Cache::get($this->clavePendientes($token), []);

        $pendientesFiltradas = array_values(array_filter(
            $pendientes,
            fn($ev) => ($ev['path'] ?? null) !== $pathABorrar
        ));

        if (count($pendientesFiltradas) === count($pendientes)) {
            return response()->json(['success' => false, 'message' => 'Esa foto ya no está pendiente (puede que ya se haya guardado o quitado desde la computadora).'], 404);
        }

        if (Storage::disk('public')->exists($pathABorrar)) {
            Storage::disk('public')->delete($pathABorrar);
        }

        Cache::put($this->clavePendientes($token), $pendientesFiltradas, now()->addMinutes(self::MINUTOS_VIGENCIA));

        $id = $datos['cabecera_monitoreo_id'];
        $slug = $datos['slug'];
        $detalle = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', $slug)
            ->first();
        $totalGuardadas = count($this->fotosReales($detalle->contenido['evidencias'] ?? []));
        $totalNuevo = $totalGuardadas + count($pendientesFiltradas);

        return response()->json([
            'success' => true,
            'total' => $totalNuevo,
            'restantes' => self::MAX_EVIDENCIAS - $totalNuevo,
        ]);
    }

    /**
     * (Autenticado, desde la laptop.) Sondeo: las fotos pendientes subidas
     * desde el celular (todavía no guardadas), para que el formulario las
     * inserte en pantalla sin recargar la página. Al guardar el formulario
     * quedan adjuntas de verdad al consultorio.
     */
    public function estado($id, $slug)
    {
        $token = Cache::get($this->claveActiva($id, $slug));
        $pendientes = $token ? Cache::get($this->clavePendientes($token), []) : [];

        return response()->json([
            'pendientes' => $pendientes,
        ]);
    }
}
