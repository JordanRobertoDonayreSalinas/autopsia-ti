<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Presencia en formularios compartidos: avisa cuando otro usuario está
 * viendo/editando el MISMO formulario al mismo tiempo (ej. dos auditores
 * abriendo el mismo consultorio), para evitar que uno pise sin darse cuenta
 * lo que el otro acaba de guardar.
 *
 * Genérico y liviano a propósito: a diferencia de la colaboración en tiempo
 * real del croquis de Infraestructura 2D (que fusiona el estado completo del
 * lienzo), aquí solo importa "quién más está aquí ahora" — no hace falta
 * mezclar datos de formulario entre usuarios. Cualquier pantalla puede
 * sumarse con su propia "clave" (un identificador único para esa instancia
 * de formulario, ej. "consultorio_{id}_{slug}").
 *
 * Igual que EvidenciaMovilController: se apoya en Cache (driver 'database'
 * en este proyecto) en vez de una tabla dedicada, guardando UNA sola entrada
 * por formulario con la lista de todos los presentes, para no depender de
 * poder enumerar claves de cache por patrón (no todos los drivers lo permiten).
 */
class FormPresenceController extends Controller
{
    /** Después de cuántos segundos sin sondear se considera que un usuario ya no está. */
    private const SEGUNDOS_INACTIVIDAD = 15;

    /** Cuánto se conserva la lista en cache como máximo (red de seguridad si nadie vuelve a sondear). */
    private const MINUTOS_VIGENCIA_CACHE = 15;

    private function clave(string $clave): string
    {
        return "presencia_formulario_{$clave}";
    }

    /**
     * (Autenticado.) Sondeo: registra que el usuario actual sigue viendo este
     * formulario y devuelve quién más lo está viendo en este momento.
     */
    public function sync(Request $request, string $clave)
    {
        $userId = auth()->id();
        $ahora = now()->timestamp;

        $presentes = Cache::get($this->clave($clave), []);

        // Se descartan los que no han vuelto a sondear a tiempo (pestaña
        // cerrada de golpe, sin red, etc.) y no llamaron a "leave".
        $limite = $ahora - self::SEGUNDOS_INACTIVIDAD;
        $presentes = array_values(array_filter($presentes, fn ($p) => ($p['last_seen'] ?? 0) >= $limite));

        // Actualiza (o agrega) la entrada del usuario actual.
        $encontrado = false;
        foreach ($presentes as &$p) {
            if ($p['user_id'] === $userId) {
                $p['last_seen'] = $ahora;
                $encontrado = true;
                break;
            }
        }
        unset($p);
        if (!$encontrado) {
            $user = auth()->user();
            $presentes[] = [
                'user_id' => $userId,
                'user_name' => $user ? $user->full_name : 'Usuario',
                'last_seen' => $ahora,
            ];
        }

        Cache::put($this->clave($clave), $presentes, now()->addMinutes(self::MINUTOS_VIGENCIA_CACHE));

        $otros = array_values(array_filter($presentes, fn ($p) => $p['user_id'] !== $userId));

        return response()->json([
            'ok' => true,
            'otros' => array_map(fn ($p) => ['user_id' => $p['user_id'], 'user_name' => $p['user_name']], $otros),
        ]);
    }

    /**
     * (Autenticado.) El usuario cierra o navega fuera del formulario: se
     * retira de inmediato de la lista, sin esperar a que expire por
     * inactividad. Se llama vía navigator.sendBeacon (no permite cabeceras
     * propias), así que el token CSRF viaja en el cuerpo.
     */
    public function leave(Request $request, string $clave)
    {
        $userId = auth()->id();
        $presentes = Cache::get($this->clave($clave), []);
        $presentes = array_values(array_filter($presentes, fn ($p) => $p['user_id'] !== $userId));

        if (empty($presentes)) {
            Cache::forget($this->clave($clave));
        } else {
            Cache::put($this->clave($clave), $presentes, now()->addMinutes(self::MINUTOS_VIGENCIA_CACHE));
        }

        return response()->json(['ok' => true]);
    }
}
