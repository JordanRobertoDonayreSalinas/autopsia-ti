<?php

namespace App\Http\Controllers;

use App\Models\CabeceraMonitoreo;
use App\Models\Reunion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Evidencia móvil (QR) para objetivos de "2 casillas fijas" en vez del
 * array dinámico contenido['evidencias'] que usa EvidenciaMovilController
 * (consultorios/RR.HH.): la foto de portada del acta
 * (CabeceraMonitoreo.foto1/foto2) y la evidencia de las Actas de Reunión
 * (Reunion.foto_1/foto_2). Mismo patrón (token en cache, página móvil
 * pública, sondeo desde la laptop, "pendiente hasta guardar el
 * formulario"), pero sin descripción por foto y con un máximo fijo de 2.
 *
 * Solo aplica en pantallas de EDICIÓN: al crear un acta/reunión todavía no
 * existe un ID al que anclar el QR, así que "Desde el Celular" solo se
 * ofrece una vez que el registro ya fue guardado por primera vez.
 */
class EvidenciaMovilFijoController extends Controller
{
    private const MINUTOS_VIGENCIA = 240; // 4 horas: duración típica de una visita
    private const MAX_FOTOS = 2;

    private const CONFIG = [
        'acta' => [
            'model' => CabeceraMonitoreo::class,
            'campos' => ['foto1', 'foto2'],
            'carpeta' => 'evidencias',
            // El valor guardado en BD no lleva 'storage/' (se agrega al mostrar).
            'prefijo_bd' => '',
            'titulo' => 'Portada del Acta',
        ],
        'reunion' => [
            'model' => Reunion::class,
            'campos' => ['foto_1', 'foto_2'],
            'carpeta' => 'reuniones',
            // El valor guardado en BD SÍ lleva 'storage/' incluido.
            'prefijo_bd' => 'storage/',
            'titulo' => 'Acta de Reunión',
        ],
    ];

    private function config(string $tipo): array
    {
        if (!isset(self::CONFIG[$tipo])) {
            abort(404, 'Tipo de evidencia móvil no reconocido.');
        }

        return self::CONFIG[$tipo];
    }

    /** Rutas "puras" (sin el prefijo de BD) de las fotos actualmente guardadas en los campos fijos. */
    private function fotosGuardadas($modelo, array $cfg): array
    {
        $fotos = [];
        foreach ($cfg['campos'] as $campo) {
            $valor = $modelo->{$campo} ?? null;
            if (!empty($valor)) {
                $fotos[] = $cfg['prefijo_bd'] && str_starts_with($valor, $cfg['prefijo_bd'])
                    ? substr($valor, strlen($cfg['prefijo_bd']))
                    : $valor;
            }
        }

        return $fotos;
    }

    /**
     * (Autenticado, desde la laptop.) Genera el token + QR para el
     * acta/reunión indicada. $tipo llega fijo vía Route::defaults() según
     * el grupo de rutas (nunca lo elige el cliente).
     */
    public function generarQr(Request $request, $id, $tipo)
    {
        try {
            $cfg = $this->config($tipo);
            $cfg['model']::findOrFail($id);

            $this->cerrarActivo($tipo, $id);

            $token = Str::random(40);
            Cache::put("evidencia_movil_fijo_{$token}", ['tipo' => $tipo, 'id' => $id], now()->addMinutes(self::MINUTOS_VIGENCIA));
            Cache::put($this->claveActiva($tipo, $id), $token, now()->addMinutes(self::MINUTOS_VIGENCIA));

            $url = route('evidencia.movil.fijo.mostrar', ['token' => $token]);
            $qrImage = QrCode::format('svg')->size(220)->color(30, 41, 59)->generate($url);

            return response()->json([
                'token' => $token,
                'url' => $url,
                'qr_html' => (string) $qrImage,
            ]);
        } catch (\Throwable $e) {
            Log::error("Error al generar QR de evidencia móvil fija ({$tipo} #{$id}): ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'No se pudo generar el código QR: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * (Autenticado — se llama internamente al guardar el formulario.)
     * Cierra/invalida el código QR activo, si lo hay.
     */
    public function cerrarActivo($tipo, $id): void
    {
        $token = Cache::pull($this->claveActiva($tipo, $id));
        if ($token) {
            Cache::forget("evidencia_movil_fijo_{$token}");
            Cache::forget($this->clavePendientes($token));
        }
    }

    private function claveActiva($tipo, $id): string
    {
        return "evidencia_movil_fijo_activo_{$tipo}_{$id}";
    }

    private function clavePendientes(string $token): string
    {
        return "evidencia_movil_fijo_pendientes_{$token}";
    }

    /**
     * (Público, sin login — accedido por el celular al escanear el QR.)
     */
    public function mostrar($token)
    {
        $datos = Cache::get("evidencia_movil_fijo_{$token}");
        abort_if(!$datos, 410, 'Este código QR ya expiró. Genera uno nuevo desde la computadora.');

        $cfg = $this->config($datos['tipo']);
        $modelo = $cfg['model']::findOrFail($datos['id']);

        return view('wizard.evidencia-movil-fijo', [
            'token' => $token,
            'titulo' => $cfg['titulo'],
            'fotosGuardadas' => $this->fotosGuardadas($modelo, $cfg),
            'fotosPendientes' => Cache::get($this->clavePendientes($token), []),
            'maxFotos' => self::MAX_FOTOS,
        ]);
    }

    /**
     * (Público, sin login.) Sube una foto. Igual que en el flujo de
     * consultorios: el archivo se guarda ya mismo en disco, pero queda como
     * "pendiente" hasta que se guarde el formulario en la laptop.
     */
    public function subir(Request $request, $token)
    {
        $datos = Cache::get("evidencia_movil_fijo_{$token}");
        if (!$datos) {
            return response()->json(['success' => false, 'message' => 'Este código QR ya expiró. Pide uno nuevo desde la computadora.'], 410);
        }

        $request->validate([
            'foto' => 'required|image|max:10240',
        ]);

        $cfg = $this->config($datos['tipo']);
        $modelo = $cfg['model']::findOrFail($datos['id']);

        $guardadas = $this->fotosGuardadas($modelo, $cfg);
        $pendientes = Cache::get($this->clavePendientes($token), []);
        $totalActual = count($guardadas) + count($pendientes);

        if ($totalActual >= self::MAX_FOTOS) {
            return response()->json(['success' => false, 'message' => 'Ya se alcanzó el máximo de '.self::MAX_FOTOS.' fotos.'], 422);
        }

        $extension = strtolower($request->file('foto')->getClientOriginalExtension() ?: 'jpg');
        $nombre = "evidencia_{$datos['tipo']}_{$datos['id']}_".($totalActual + 1).'_'.date('Ymd_His').'_'.uniqid().'.'.$extension;
        $path = $request->file('foto')->storeAs($cfg['carpeta'], $nombre, 'public');

        $pendientes[] = ['path' => $path];
        Cache::put($this->clavePendientes($token), $pendientes, now()->addMinutes(self::MINUTOS_VIGENCIA));

        $totalNuevo = count($guardadas) + count($pendientes);

        return response()->json([
            'success' => true,
            'path' => $path,
            'total' => $totalNuevo,
            'restantes' => self::MAX_FOTOS - $totalNuevo,
        ]);
    }

    /**
     * (Público, sin login.) Quita una foto pendiente (todavía no guardada).
     * Las fotos ya guardadas no se pueden borrar desde aquí.
     */
    public function eliminar(Request $request, $token)
    {
        $datos = Cache::get("evidencia_movil_fijo_{$token}");
        if (!$datos) {
            return response()->json(['success' => false, 'message' => 'Este código QR ya expiró.'], 410);
        }

        $request->validate([
            'path' => 'required|string',
        ]);

        $pathABorrar = $request->input('path');
        $pendientes = Cache::get($this->clavePendientes($token), []);

        $filtradas = array_values(array_filter(
            $pendientes,
            fn ($p) => ($p['path'] ?? null) !== $pathABorrar
        ));

        if (count($filtradas) === count($pendientes)) {
            return response()->json(['success' => false, 'message' => 'Esa foto ya no está pendiente (puede que ya se haya guardado o quitado desde la computadora).'], 404);
        }

        if (Storage::disk('public')->exists($pathABorrar)) {
            Storage::disk('public')->delete($pathABorrar);
        }

        Cache::put($this->clavePendientes($token), $filtradas, now()->addMinutes(self::MINUTOS_VIGENCIA));

        $cfg = $this->config($datos['tipo']);
        $modelo = $cfg['model']::find($datos['id']);
        $totalGuardadas = $modelo ? count($this->fotosGuardadas($modelo, $cfg)) : 0;
        $totalNuevo = $totalGuardadas + count($filtradas);

        return response()->json([
            'success' => true,
            'total' => $totalNuevo,
            'restantes' => self::MAX_FOTOS - $totalNuevo,
        ]);
    }

    /**
     * (Autenticado, desde la laptop.) Sondeo: las fotos pendientes subidas
     * desde el celular (todavía no guardadas).
     */
    public function estado($id, $tipo)
    {
        $token = Cache::get($this->claveActiva($tipo, $id));
        $pendientes = $token ? Cache::get($this->clavePendientes($token), []) : [];

        return response()->json([
            'pendientes' => $pendientes,
        ]);
    }
}
