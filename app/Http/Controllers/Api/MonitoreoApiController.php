<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CabeceraMonitoreo;
use App\Models\Establecimiento;
use App\Models\EquipoComputo;
use App\Models\MonitoreoModulos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Espejo API (Sanctum) de MonitoreoController::index — el listado real de
 * "Actas de Diagnóstico Situacional" (no confundir con el flujo de crear
 * una acta nueva, que es una pantalla aparte). Mismos filtros, mismos
 * contadores, mismo cálculo de "módulos firmados" (copiado tal cual del
 * bloque @php de resources/views/usuario/monitoreo/index.blade.php).
 */
class MonitoreoApiController extends Controller
{
    private const MODULOS_ESTANDAR = [
        'gestion_administrativa', 'citas', 'triaje', 'consulta_medicina', 'consulta_odontologia',
        'consulta_nutricion', 'consulta_psicologia', 'cred', 'inmunizaciones', 'atencion_prenatal',
        'planificacion_familiar', 'parto', 'puerperio', 'fua_electronico', 'farmacia', 'referencias',
        'laboratorio', 'urgencias',
    ];

    private const SUBMODULOS_SALUD_MENTAL = [
        'sm_medicina_general', 'sm_psiquiatria', 'sm_med_familiar', 'sm_psicologia',
        'sm_enfermeria', 'sm_servicio_social', 'sm_terapias',
    ];

    /**
     * Réplica exacta del bloque @php de index.blade.php (líneas 289-334):
     * calcula módulos firmados excluyendo/agregando el grupo de Salud
     * Mental como una unidad. Devuelve [firmados, total, submodulosSM].
     */
    private function calcularModulosFirmados($detalles): array
    {
        $configMod = $detalles->where('modulo_nombre', 'config_modulos')->first();
        $activosKeys = $configMod
            ? (is_array($configMod->contenido) ? $configMod->contenido : (json_decode($configMod->contenido, true) ?? []))
            : self::MODULOS_ESTANDAR;
        $activosKeys = array_values(array_filter((array) $activosKeys));

        $saludMentalHabilitado = in_array('salud_mental_group', $activosKeys, true);
        $submodulosSMHabilitados = array_values(array_intersect($activosKeys, self::SUBMODULOS_SALUD_MENTAL));

        if ($saludMentalHabilitado) {
            $activosKeys = array_values(array_diff($activosKeys, self::SUBMODULOS_SALUD_MENTAL));
        }

        $modulosNoSM = array_diff($activosKeys, ['salud_mental_group']);
        $firmadosCount = $detalles->filter(fn ($d) => in_array($d->modulo_nombre, $modulosNoSM, true) && !empty($d->pdf_firmado_path))->count();

        $submodulosSM = null;
        if ($saludMentalHabilitado) {
            $totalSMHabilitados = count($submodulosSMHabilitados);
            $firmadosSMHabilitados = $detalles->filter(fn ($d) => in_array($d->modulo_nombre, $submodulosSMHabilitados, true) && !empty($d->pdf_firmado_path))->count();

            if ($totalSMHabilitados > 0 && $firmadosSMHabilitados === $totalSMHabilitados) {
                $firmadosCount++;
            }
            $submodulosSM = ['firmados' => $firmadosSMHabilitados, 'total' => $totalSMHabilitados];
        }

        $totalHabilitados = count($activosKeys);

        return [
            'firmados' => $firmadosCount,
            'total' => $totalHabilitados,
            'porcentaje' => $totalHabilitados > 0 ? round(($firmadosCount / $totalHabilitados) * 100) : 0,
            'submodulos_salud_mental' => $submodulosSM,
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
        $fechaFin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

        $query = CabeceraMonitoreo::with(['establecimiento', 'detalles', 'user']);

        // Igual que la web: el rol operador solo ve sus propias actas.
        if ($user->role === 'operador') {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('implementador')) $query->where('implementador', $request->input('implementador'));
        if ($request->filled('provincia')) $query->whereHas('establecimiento', fn ($q) => $q->where('provincia', $request->input('provincia')));
        if ($request->filled('distrito')) $query->whereHas('establecimiento', fn ($q) => $q->where('distrito', $request->input('distrito')));
        if ($request->filled('establecimiento_id')) $query->where('establecimiento_id', $request->input('establecimiento_id'));

        if ($request->filled('estado')) {
            if ($request->input('estado') === 'firmada') $query->where('firmado', 1);
            elseif ($request->input('estado') === 'pendiente') $query->where('firmado', 0);
        }

        $visibilidad = $request->input('estado_anulado', 'todos');
        if ($visibilidad === 'anulado') {
            $query->where('anulado', 1);
        } elseif ($visibilidad === 'activo') {
            $query->where(fn ($q) => $q->where('anulado', 0)->orWhereNull('anulado'));
        }

        $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);

        $queryCount = clone $query;
        $countCompletados = (clone $queryCount)->where('firmado', 1)->where(fn ($q) => $q->where('anulado', 0)->orWhereNull('anulado'))->count();
        $countPendientes = (clone $queryCount)->where(fn ($q) => $q->where('firmado', 0)->orWhereNull('firmado'))->where(fn ($q) => $q->where('anulado', 0)->orWhereNull('anulado'))->count();
        $countAnuladas = (clone $queryCount)->where('anulado', 1)->count();

        $pagina = $query->orderByDesc('fecha')->orderByDesc('id')->paginate(10);

        $actas = collect($pagina->items())->map(function (CabeceraMonitoreo $m) {
            $mod = $this->calcularModulosFirmados($m->detalles);
            $implementador = $m->implementador
                ?: ($m->user ? mb_strtoupper(trim("{$m->user->apellido_paterno} {$m->user->apellido_materno} {$m->user->name}"), 'UTF-8') : null);

            return [
                'id' => $m->id,
                'numero_acta' => str_pad((string) $m->numero_acta, 5, '0', STR_PAD_LEFT),
                'tipo_origen' => $m->tipo_origen,
                'fecha' => optional($m->fecha instanceof \DateTimeInterface ? $m->fecha : Carbon::parse($m->fecha))->format('d/m/Y'),
                'establecimiento_id' => $m->establecimiento_id,
                'establecimiento_nombre' => $m->establecimiento->nombre ?? '—',
                'provincia' => $m->establecimiento->provincia ?? '—',
                'distrito' => $m->establecimiento->distrito ?? '—',
                'categoria_congelada' => $m->categoria_congelada,
                'anulado' => (bool) $m->anulado,
                'implementador' => $implementador,
                'modulos_firmados' => ['firmados' => $mod['firmados'], 'total' => $mod['total'], 'porcentaje' => $mod['porcentaje']],
                'submodulos_salud_mental' => $mod['submodulos_salud_mental'],
                'firmado' => (bool) $m->firmado,
                'firmado_pdf' => $m->firmado_pdf,
            ];
        });

        $provincias = Establecimiento::whereHas('monitoreos.detalles')->distinct()->pluck('provincia')->filter()->sort()->values();
        $distritos = $request->filled('provincia')
            ? Establecimiento::where('provincia', $request->input('provincia'))->whereHas('monitoreos.detalles')->distinct()->pluck('distrito')->filter()->sort()->values()
            : collect();
        $establecimientos = Establecimiento::whereHas('monitoreos.detalles')
            ->when($request->filled('provincia'), fn ($q) => $q->where('provincia', $request->input('provincia')))
            ->when($request->filled('distrito'), fn ($q) => $q->where('distrito', $request->input('distrito')))
            ->orderBy('nombre')->get(['id', 'nombre']);
        $implementadores = CabeceraMonitoreo::distinct()->pluck('implementador')->filter()->values();

        return response()->json([
            'success' => true,
            'actas' => $actas,
            'current_page' => $pagina->currentPage(),
            'last_page' => $pagina->lastPage(),
            'total' => $pagina->total(),
            'counts' => ['completados' => $countCompletados, 'pendientes' => $countPendientes, 'anuladas' => $countAnuladas],
            'filtros' => [
                'implementadores' => $implementadores,
                'provincias' => $provincias,
                'distritos' => $distritos,
                'establecimientos' => $establecimientos,
            ],
            'mostrar_filtros' => $user->role !== 'operador',
        ]);
    }

    /**
     * Detalle completo de una acta (cabecera + módulos + equipos + equipo
     * de monitoreo) para poder editarla desde la app aunque se haya
     * creado en la web o en otro dispositivo — la única condición real es
     * que el usuario pueda VERLA (misma regla de visibilidad que index():
     * el operador solo ve/edita las suyas, el resto ve/edita todas).
     */
    public function show(Request $request, $id): JsonResponse
    {
        $m = CabeceraMonitoreo::with(['establecimiento', 'equipo', 'detalles'])->findOrFail($id);

        if ($request->user()->role === 'operador' && $m->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso a esta acta.'], 403);
        }

        $equipos = EquipoComputo::where('cabecera_monitoreo_id', $id)->get();

        return response()->json([
            'success' => true,
            'acta' => [
                'id' => $m->id,
                'establecimiento_id' => $m->establecimiento_id,
                'establecimiento_nombre' => $m->establecimiento->nombre ?? null,
                'fecha' => optional($m->fecha instanceof \DateTimeInterface ? $m->fecha : Carbon::parse($m->fecha))->format('Y-m-d'),
                'responsable' => $m->responsable,
                'implementador' => $m->implementador,
                'categoria_congelada' => $m->categoria_congelada,
                'tipo_origen' => $m->tipo_origen,
                'numero_acta' => $m->numero_acta,
                'anulado' => (bool) $m->anulado,
                'firmado' => (bool) $m->firmado,
                'firmado_pdf' => $m->firmado_pdf,
                'foto1' => $m->foto1,
                'foto2' => $m->foto2,
                'pozo_tierra' => $m->pozo_tierra,
                'pozo_tierra_cantidad' => $m->pozo_tierra_cantidad,
                'pozo_tierra_operativos' => $m->pozo_tierra_operativos,
                'pozo_tierra_inoperativos' => $m->pozo_tierra_inoperativos,
                'panel_solar' => $m->panel_solar,
                'panel_solar_cantidad' => $m->panel_solar_cantidad,
                'panel_solar_operativos' => $m->panel_solar_operativos,
                'panel_solar_inoperativos' => $m->panel_solar_inoperativos,
            ],
            'modulos' => $m->detalles->map(fn ($d) => [
                'modulo_nombre' => $d->modulo_nombre,
                'contenido' => $d->contenido,
                'pdf_firmado_path' => $d->pdf_firmado_path,
            ]),
            'equipo_monitoreo' => $m->equipo->map(fn ($e) => [
                'tipo_doc' => $e->tipo_doc, 'doc' => $e->doc, 'apellido_paterno' => $e->apellido_paterno,
                'apellido_materno' => $e->apellido_materno, 'nombres' => $e->nombres, 'cargo' => $e->cargo,
                'institucion' => $e->institucion,
            ]),
            'equipos' => $equipos->map(fn ($e) => [
                'modulo' => $e->modulo, 'descripcion' => $e->descripcion, 'cantidad' => $e->cantidad,
                'estado' => $e->estado, 'nro_serie' => $e->nro_serie, 'propio' => $e->propio,
                'observacion' => $e->observacion, 'especificaciones' => $e->especificaciones,
            ]),
        ]);
    }

    /**
     * Guarda (crea o actualiza) UN módulo de una acta YA EXISTENTE en el
     * servidor — a diferencia de OfflineSyncController::sincronizarLoteOffline
     * (que siempre CREA una cabecera nueva, pensado solo para actas nacidas
     * offline), este endpoint actualiza en el sitio, así que sirve tanto
     * para actas descargadas para editar como para actas que ya terminaron
     * su sincronización inicial y se siguen editando después.
     */
    public function guardarModulo(Request $request, $id): JsonResponse
    {
        $acta = CabeceraMonitoreo::findOrFail($id);

        if ($request->user()->role === 'operador' && $acta->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'No tiene acceso a esta acta.'], 403);
        }

        $moduloNombre = $request->input('modulo_nombre');
        if (empty($moduloNombre)) {
            return response()->json(['success' => false, 'message' => 'Falta modulo_nombre.'], 422);
        }

        $contenido = $request->input('contenido', []);
        if (is_string($contenido)) {
            $contenido = json_decode($contenido, true) ?? [];
        }

        DB::transaction(function () use ($acta, $moduloNombre, $contenido, $request) {
            MonitoreoModulos::updateOrCreate(
                ['cabecera_monitoreo_id' => $acta->id, 'modulo_nombre' => $moduloNombre],
                ['contenido' => $contenido]
            );

            // Mismo patrón que el sync offline: se reemplazan por completo
            // los equipos de este módulo con lo que mande el cliente.
            if ($request->has('equipos')) {
                EquipoComputo::where('cabecera_monitoreo_id', $acta->id)->where('modulo', $moduloNombre)->delete();
                foreach ((array) $request->input('equipos', []) as $eq) {
                    if (empty($eq['descripcion'])) continue;
                    EquipoComputo::create([
                        'cabecera_monitoreo_id' => $acta->id,
                        'modulo' => $moduloNombre,
                        'descripcion' => mb_strtoupper(trim($eq['descripcion'] ?? '')),
                        'cantidad' => (int) ($eq['cantidad'] ?? 1),
                        'estado' => mb_strtoupper(trim($eq['estado'] ?? 'OPERATIVO')),
                        'propio' => mb_strtoupper(trim($eq['propio'] ?? 'EXCLUSIVO')),
                        'nro_serie' => mb_strtoupper(trim($eq['nro_serie'] ?? $eq['serie'] ?? '')),
                        'observacion' => mb_strtoupper(trim($eq['observacion'] ?? '')),
                        'especificaciones' => $eq['especificaciones'] ?? null,
                    ]);
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Módulo guardado correctamente.']);
    }

    /**
     * Alterna anulado/activo — mismo contrato que MonitoreoController::anular
     * (esa ruta web no está registrada en routes/web.php, es un método sin
     * ruta; acá se expone deliberadamente porque es la intención clara del
     * código, no la omisión).
     */
    public function anular(Request $request, $id): JsonResponse
    {
        $monitoreo = CabeceraMonitoreo::findOrFail($id);

        if ($request->user()->role === 'operador' && $monitoreo->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'No puede anular actas de otros usuarios.'], 403);
        }

        $monitoreo->anulado = !$monitoreo->anulado;
        $monitoreo->save();

        return response()->json([
            'success' => true,
            'anulado' => (bool) $monitoreo->anulado,
            'message' => $monitoreo->anulado ? 'Acta anulada correctamente.' : 'Acta reactivada correctamente.',
        ]);
    }
}
