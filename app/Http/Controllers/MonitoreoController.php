<?php

namespace App\Http\Controllers;

use App\Models\CabeceraMonitoreo;
use App\Models\Establecimiento;
use App\Models\MonitoreoModulos;
use App\Models\MonitoreoEquipo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use App\Mail\ActaMonitoreoMail;

class MonitoreoController extends Controller
{
    /**
     * Función auxiliar para determinar si un establecimiento es IPRESS ESPECIALIZADA (CSMC).
     */
    private function esEspecializada($establecimiento)
    {
        $codigosCSMC = ['25933', '28653', '27197', '34021', '25977', '33478', '27199', '30478'];

        $nombresCSMC = [
            'CSMC TUPAC AMARU',
            'CSMC COLOR ESPERANZA',
            'CSMC DECÍDETE A SER FELIZ',
            'CSMC SANTISIMA VIRGEN DE YAUCA',
            'CSMC VITALIZA',
            'CSMC CRISTO MORENO DE LUREN',
            'CSMC NUEVO HORIZONTE',
            'CSMC MENTE SANA'
        ];

        return in_array($establecimiento->codigo, $codigosCSMC) ||
            in_array(strtoupper(trim($establecimiento->nombre)), $nombresCSMC);
    }

    /**
     * Listado principal: Muestra todos los monitoreos.
     */
    public function index(Request $request)
    {
        $fecha_inicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
        $fecha_fin = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

        $query = CabeceraMonitoreo::with(['establecimiento', 'equipo', 'detalles', 'user']);

        // El operador solo ve sus propias actas
        if (Auth::user()->role === 'operador') {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('implementador')) {
            $query->where('implementador', $request->input('implementador'));
        }

        if ($request->filled('provincia')) {
            $query->whereHas('establecimiento', function ($q) use ($request) {
                $q->where('provincia', $request->input('provincia'));
            });
        }

        if ($request->filled('distrito')) {
            $query->whereHas('establecimiento', function ($q) use ($request) {
                $q->where('distrito', $request->input('distrito'));
            });
        }

        if ($request->filled('establecimiento_id')) {
            $query->where('establecimiento_id', $request->input('establecimiento_id'));
        }

        if ($request->filled('estado')) {
            if ($request->estado == 'firmada') {
                $query->where('firmado', 1);
            } elseif ($request->estado == 'pendiente') {
                $query->where('firmado', 0);
            }
        }

        // Filtro de visibilidad (anulado)
        if ($request->filled('estado_anulado')) {
            $val = $request->input('estado_anulado');
            if ($val == 'anulado') {
                $query->where('anulado', 1);
            } elseif ($val == 'activo') {
                $query->where(function ($q) {
                    $q->where('anulado', 0)->orWhereNull('anulado');
                });
            }
            // 'todos' = sin filtro, muestra todo
        }
        // Sin filtro: muestra todo (incluyendo anuladas con estilo especial)

        $query->whereBetween('fecha', [$fecha_inicio, $fecha_fin]);

        // Clonar ANTES de paginar para que los contadores no hereden el LIMIT/OFFSET de la paginación
        $queryCount = clone $query;

        $monitoreos = $query->orderByDesc('fecha')->orderByDesc('id')->paginate(10)->appends($request->query());

        // Contadores optimizados (usan el clon sin paginación)
        $countCompletados = (clone $queryCount)->where('firmado', 1)->where(function($q){ $q->where('anulado',0)->orWhereNull('anulado'); })->count();
        $countPendientes  = (clone $queryCount)->where(function($q){ $q->where('firmado',0)->orWhereNull('firmado'); })->where(function($q){ $q->where('anulado',0)->orWhereNull('anulado'); })->count();
        $countAnuladas    = (clone $queryCount)->where('anulado', 1)->count();

        $implementadores = CabeceraMonitoreo::distinct()->pluck('implementador');
        $provincias = Establecimiento::whereHas('monitoreos.detalles')
            ->distinct()
            ->pluck('provincia')
            ->filter()
            ->sort();

        $distritos = $request->filled('provincia') ? Establecimiento::where('provincia', $request->provincia)
            ->whereHas('monitoreos.detalles')
            ->distinct()
            ->pluck('distrito')
            ->filter()
            ->sort() : [];

        $establecimientos = Establecimiento::whereHas('monitoreos.detalles')
            ->when($request->provincia, fn($q) => $q->where('provincia', $request->provincia))
            ->when($request->distrito, fn($q) => $q->where('distrito', $request->distrito))
            ->orderBy('nombre')
            ->get();

        $usuariosActivos = [];
        if (Auth::user()->role === 'admin') {
            $usuariosActivos = User::whereIn('username', [
                '70314306', // JAIRO MELGAR MESIAS
                '70398441', // ERNESTO JAVIER MUÑANTE MEDINA
                '71883059', // JORDAN ROBERTO DONAYRE SALINAS
                '70073797', // JUAN CARLOS GUTIERREZ HILARIO
                '72762954'  // LIDA GRACIELA YAÑEZ MEDINA
            ])
            ->where('status', 'active')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('name')
            ->get();
        }

        return view('usuario.monitoreo.index', compact(
            'monitoreos',
            'countCompletados',
            'countPendientes',
            'countAnuladas',
            'implementadores',
            'provincias',
            'distritos',
            'establecimientos',
            'fecha_inicio',
            'fecha_fin',
            'usuariosActivos'
        ));
    }

    /**
     * Anula o reactiva un acta de monitoreo.
     */
    public function anular($id)
    {
        try {
            $monitoreo = CabeceraMonitoreo::findOrFail($id);
            $monitoreo->anulado = !$monitoreo->anulado;
            $monitoreo->save();

            return response()->json([
                'success' => true,
                'anulado' => $monitoreo->anulado,
                'message' => $monitoreo->anulado ? 'Acta anulada correctamente.' : 'Acta reactivada correctamente.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ajaxGetDistritos(Request $request)
    {
        $distritos = Establecimiento::whereHas('monitoreos.detalles')
            ->when($request->provincia, fn($q) => $q->where('provincia', $request->provincia))
            ->distinct()
            ->pluck('distrito')
            ->filter()
            ->sort()
            ->values();

        return response()->json($distritos);
    }

    public function ajaxGetEstablecimientos(Request $request)
    {
        $establecimientos = Establecimiento::whereHas('monitoreos.detalles')
            ->when($request->provincia, fn($q) => $q->where('provincia', $request->provincia))
            ->when($request->distrito, fn($q) => $q->where('distrito', $request->distrito))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($establecimientos);
    }

    public function create()
    {
        $usuarios = User::orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('name')
            ->get();

        return view('usuario.monitoreo.create', compact('usuarios'));
    }

    public function buscarFiltro(Request $request)
    {
        $term = trim($request->term);
        if (empty($term))
            return response()->json([]);

        $equipo = MonitoreoEquipo::select(
            'doc',
            DB::raw('MAX(tipo_doc) as tipo_doc'),
            DB::raw('MAX(apellido_paterno) as apellido_paterno'),
            DB::raw('MAX(apellido_materno) as apellido_materno'),
            DB::raw('MAX(nombres) as nombres'),
            DB::raw('MAX(cargo) as cargo'),
            DB::raw('MAX(institucion) as institucion')
        )
            ->where(function ($q) use ($term) {
                $q->where('doc', 'LIKE', "%$term%")
                    ->orWhere('apellido_paterno', 'LIKE', "%$term%");
            })
            ->groupBy('doc')
            ->limit(10)
            ->get();

        return response()->json($equipo);
    }

    public function buscarMiembroEquipo($doc)
    {
        try {
            $miembro = MonitoreoEquipo::where('doc', trim($doc))
                ->orderBy('created_at', 'desc')
                ->first();

            if ($miembro) {
                return response()->json([
                    'exists' => true,
                    'doc' => $miembro->doc,
                    'apellido_paterno' => $miembro->apellido_paterno,
                    'apellido_materno' => $miembro->apellido_materno,
                    'nombres' => $miembro->nombres,
                    'cargo' => $miembro->cargo,
                    'institucion' => $miembro->institucion
                ]);
            }
            return response()->json(['exists' => false]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Búsqueda de profesional / participante por DNI o Documento.
     * 1. Consulta la tabla local mon_profesionales y mon_equipo_monitoreo.
     * 2. Si no lo encuentra y no es local_only, consulta DecolectaService (RENIEC).
     */
    public function buscarProfesional(Request $request, $doc)
    {
        $doc = trim($doc);
        $localOnly = $request->has('local_only');

        if (empty($doc)) {
            return response()->json(['exists' => false, 'exists_external' => false]);
        }

        // 1. Buscar en la tabla local mon_profesionales
        $profesional = \App\Models\Profesional::where('doc', $doc)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($profesional) {
            return response()->json([
                'exists'           => true,
                'doc'              => $profesional->doc,
                'tipo_doc'         => $profesional->tipo_doc ?? 'DNI',
                'apellido_paterno' => $profesional->apellido_paterno,
                'apellido_materno' => $profesional->apellido_materno,
                'nombres'          => $profesional->nombres,
                'cargo'            => $profesional->cargo ?? '',
                'email'            => $profesional->email ?? '',
                'telefono'         => $profesional->telefono ?? '',
            ]);
        }

        // 1b. Buscar alternativamente en equipo de monitoreo previo
        $equipo = MonitoreoEquipo::where('doc', $doc)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($equipo) {
            return response()->json([
                'exists'           => true,
                'doc'              => $equipo->doc,
                'tipo_doc'         => $equipo->tipo_doc ?? 'DNI',
                'apellido_paterno' => $equipo->apellido_paterno,
                'apellido_materno' => $equipo->apellido_materno,
                'nombres'          => $equipo->nombres,
                'cargo'            => $equipo->cargo ?? '',
                'institucion'      => $equipo->institucion ?? '',
            ]);
        }

        // Si se solicita sólo búsqueda local
        if ($localOnly) {
            return response()->json(['exists' => false, 'exists_external' => false]);
        }

        // 2. Consulta a DecolectaService (RENIEC) para DNI de 8 dígitos
        if (preg_match('/^\d{8}$/', $doc)) {
            try {
                $decolecta = new \App\Services\DecolectaService();
                $result = $decolecta->consultarDni($doc);

                if (isset($result['error']) && $result['error'] === 'quota_exceeded') {
                    return response()->json([
                        'exists'           => false,
                        'exists_external'  => false,
                        'quota_exceeded'   => true,
                        'remaining_tokens' => 0
                    ]);
                }

                if (!empty($result['success']) && !empty($result['data'])) {
                    $d = $result['data'];
                    return response()->json([
                        'exists'           => false,
                        'exists_external'  => true,
                        'fuente'           => $result['fuente'] ?? 'reniec',
                        'doc'              => $doc,
                        'tipo_doc'         => 'DNI',
                        'nombres'          => $d['nombres'] ?? '',
                        'apellido_paterno' => $d['apellido_paterno'] ?? ($d['paterno'] ?? ''),
                        'apellido_materno' => $d['apellido_materno'] ?? ($d['materno'] ?? ''),
                        'email'            => $d['email'] ?? '',
                        'telefono'         => $d['telefono'] ?? '',
                        'remaining_tokens' => $result['remaining_tokens'] ?? null
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error("Error en buscarProfesional Decolecta: " . $e->getMessage());
            }
        }

        return response()->json(['exists' => false, 'exists_external' => false]);
    }

    /**
     * GUARDAR PASO 1 (CREACIÓN DE ACTA)
     * LÓGICA DE NUMERACIÓN INDEPENDIENTE CORREGIDA
     */
    public function store(Request $request)
    {
        $request->validate([
            'establecimiento_id' => 'required|exists:establecimientos,id',
            'fecha' => 'required|date',
            'responsable' => 'required|string|max:255',
            'categoria' => 'nullable|string|max:50',
            'implementador' => 'required|string',
            'equipo' => 'required|array|min:1',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $establecimiento = Establecimiento::findOrFail($request->establecimiento_id);

            // Actualizar datos maestros del establecimiento
            $establecimiento->update([
                'responsable' => mb_strtoupper(trim($request->responsable)),
                'categoria' => mb_strtoupper(trim($request->categoria)),
            ]);

            // 1. Determinar el Tipo de Origen (CORREGIDO: 'NO ESPECIALIZADA')
            $esEspecializada = $this->esEspecializada($establecimiento);
            $tipoOrigen = $esEspecializada ? 'ESPECIALIZADA' : 'NO ESPECIALIZADA';

            // 2. Calcular Numeración Independiente
            // Buscamos el último número PERO SOLO del mismo tipo_origen
            // Si es ESPECIALIZADA cuenta sus propias actas, si es NO ESPECIALIZADA cuenta las suyas aparte.
            $ultimoNumero = CabeceraMonitoreo::where('tipo_origen', $tipoOrigen)->max('numero_acta');

            // Si no existe ninguno de ese tipo, empezamos en 1, sino sumamos 1
            $nuevoNumero = $ultimoNumero ? ($ultimoNumero + 1) : 1;

            // 3. Crear Cabecera
            $monitoreo = new CabeceraMonitoreo();
            $monitoreo->fecha = $request->fecha;
            $monitoreo->establecimiento_id = $request->establecimiento_id;
            $monitoreo->tipo_origen = $tipoOrigen;
            $monitoreo->numero_acta = $nuevoNumero; // Guardamos el correlativo independiente
            $monitoreo->responsable = mb_strtoupper(trim($request->responsable));
            $monitoreo->categoria_congelada = mb_strtoupper(trim($request->categoria));
            $monitoreo->implementador = mb_strtoupper(trim($request->implementador));
            $monitoreo->pozo_tierra = $request->input('pozo_tierra', 'NO');
            $monitoreo->pozo_tierra_cantidad = $request->input('pozo_tierra') === 'SI' ? $request->input('pozo_tierra_cantidad') : null;
            $monitoreo->pozo_tierra_operativos = $request->input('pozo_tierra') === 'SI' ? $request->input('pozo_tierra_operativos') : null;
            $monitoreo->pozo_tierra_inoperativos = $request->input('pozo_tierra') === 'SI' ? $request->input('pozo_tierra_inoperativos') : null;
            $monitoreo->panel_solar = $request->input('panel_solar', 'NO');
            $monitoreo->panel_solar_cantidad = $request->input('panel_solar') === 'SI' ? $request->input('panel_solar_cantidad') : null;
            $monitoreo->panel_solar_operativos = $request->input('panel_solar') === 'SI' ? $request->input('panel_solar_operativos') : null;
            $monitoreo->panel_solar_inoperativos = $request->input('panel_solar') === 'SI' ? $request->input('panel_solar_inoperativos') : null;
            $monitoreo->user_id = Auth::id();

            // Guardar fotos
            if ($request->hasFile('imagenes')) {
                $files = $request->file('imagenes');
                if (isset($files[0])) {
                    $nombreBase1 = 'acta_nueva_foto1_' . date('Ymd_His') . '_' . uniqid();
                    $monitoreo->foto1 = \App\Helpers\ImagenHelper::guardarComprimida($files[0], 'evidencias', $nombreBase1, 'public');
                }
                if (isset($files[1])) {
                    $nombreBase2 = 'acta_nueva_foto2_' . date('Ymd_His') . '_' . uniqid();
                    $monitoreo->foto2 = \App\Helpers\ImagenHelper::guardarComprimida($files[1], 'evidencias', $nombreBase2, 'public');
                }
            }

            $monitoreo->save();

            // 4. Guardar Equipo
            foreach ($request->equipo as $persona) {
                if (!empty($persona['doc'])) {
                    MonitoreoEquipo::create([
                        'cabecera_monitoreo_id' => $monitoreo->id,
                        'tipo_doc' => $persona['tipo_doc'] ?? 'DNI',
                        'doc' => trim($persona['doc']),
                        'apellido_paterno' => mb_strtoupper(trim($persona['apellido_paterno'])),
                        'apellido_materno' => mb_strtoupper(trim($persona['apellido_materno'])),
                        'nombres' => mb_strtoupper(trim($persona['nombres'])),
                        'cargo' => mb_strtoupper(trim($persona['cargo'] ?? 'MONITOR')),
                        'institucion' => mb_strtoupper(trim($persona['institucion'] ?? 'DIRESA')),
                    ]);
                }
            }

            DB::commit();

            $msjExito = "Acta {$tipoOrigen} N° " . str_pad($nuevoNumero, 5, '0', STR_PAD_LEFT) . " generada con éxito.";
            return redirect()->route('usuario.monitoreo.modulos', $monitoreo->id)->with('success', $msjExito);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error al guardar: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * UPDATE
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'establecimiento_id' => 'required|exists:establecimientos,id',
            'fecha' => 'required|date',
            'responsable' => 'required|string|max:255',
            'equipo' => 'required|array|min:1',
            'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        try {
            DB::beginTransaction();
            $monitoreo = CabeceraMonitoreo::findOrFail($id);

            $monitoreo->fecha = $request->fecha;
            $monitoreo->establecimiento_id = $request->establecimiento_id;
            $monitoreo->responsable = mb_strtoupper(trim($request->responsable));
            $monitoreo->categoria_congelada = mb_strtoupper(trim($request->categoria));
            $monitoreo->implementador = mb_strtoupper(trim($request->implementador));

            if ($request->hasFile('imagenes')) {
                $files = $request->file('imagenes');
                if (isset($files[0])) {
                    if ($monitoreo->foto1)
                        Storage::disk('public')->delete($monitoreo->foto1);
                    $nombreBase1 = "acta_{$monitoreo->id}_foto1_" . date('Ymd_His') . '_' . uniqid();
                    $monitoreo->foto1 = \App\Helpers\ImagenHelper::guardarComprimida($files[0], 'evidencias', $nombreBase1, 'public');
                }
                if (isset($files[1])) {
                    if ($monitoreo->foto2)
                        Storage::disk('public')->delete($monitoreo->foto2);
                    $nombreBase2 = "acta_{$monitoreo->id}_foto2_" . date('Ymd_His') . '_' . uniqid();
                    $monitoreo->foto2 = \App\Helpers\ImagenHelper::guardarComprimida($files[1], 'evidencias', $nombreBase2, 'public');
                }
            }

            $monitoreo->save();

            MonitoreoEquipo::where('cabecera_monitoreo_id', $id)->delete();
            foreach ($request->equipo as $persona) {
                if (!empty($persona['doc'])) {
                    MonitoreoEquipo::create([
                        'cabecera_monitoreo_id' => $monitoreo->id,
                        'tipo_doc' => $persona['tipo_doc'] ?? 'DNI',
                        'doc' => trim($persona['doc']),
                        'apellido_paterno' => mb_strtoupper(trim($persona['apellido_paterno'])),
                        'apellido_materno' => mb_strtoupper(trim($persona['apellido_materno'])),
                        'nombres' => mb_strtoupper(trim($persona['nombres'])),
                        'cargo' => mb_strtoupper(trim($persona['cargo'])),
                        'institucion' => mb_strtoupper(trim($persona['institucion'])),
                    ]);
                }
            }

            DB::commit();

            if ($request->redirect_to === 'modulos') {
                return redirect()->route('usuario.monitoreo.modulos', $monitoreo->id)->with('success', 'Cabecera actualizada.');
            }
            return redirect()->route('usuario.monitoreo.index')->with('success', 'Acta actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * GESTIÓN DE MÓDULOS (NIVEL 1)
     */
    public function gestionarModulos($id)
    {
        $acta = CabeceraMonitoreo::with(['establecimiento', 'equipo'])->findOrFail($id);


        $esEspecializada = ($acta->tipo_origen === 'ESPECIALIZADA') || $this->esEspecializada($acta->establecimiento);

        // Datos de estado para Nivel 1
        $modulosGuardados = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', '!=', 'config_modulos')
            ->pluck('modulo_nombre')->toArray();

        $modulosFirmados = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->whereNotNull('pdf_firmado_path')
            ->pluck('modulo_nombre')->toArray();

        $config = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', 'config_modulos')->first();

        $modulosActivos = $config ? $config->contenido : [];

        if ($esEspecializada) {
            // LISTA PRINCIPAL CSMC (Nivel 1)
            // 'salud_mental_group' llevará al sub-menú (Controlador gestionarSaludMental)
            $modulosMaster = [
                'gestion_admin_esp' => ['nombre' => '1. GESTION ADMINISTRATIVA', 'icon' => 'folder-kanban'],
                'citas_esp' => ['nombre' => '2. CITAS', 'icon' => 'calendar-clock'],
                'triaje_esp' => ['nombre' => '3. TRIAJE', 'icon' => 'clipboard-pulse'],
                'salud_mental_group' => ['nombre' => '4. SALUD MENTAL', 'icon' => 'brain-circuit'], // Contenedor Nivel 2
                'toma_muestra' => ['nombre' => '5. TOMA DE MUESTRA', 'icon' => 'test-tube'],
                'farmacia_esp' => ['nombre' => '6. FARMACIA', 'icon' => 'pill'],
            ];

            return view('usuario.monitoreo.modulos_especializados', compact(
                'acta',
                'modulosMaster',
                'modulosGuardados',
                'modulosActivos',
                'modulosFirmados'
            ));
        } else {
            // LISTA ESTÁNDAR (NO ESPECIALIZADA)
            $modulosMaster = [
                'gestion_administrativa' => ['nombre' => '01. Gestión Administrativa', 'icon' => 'folder-kanban'],
                'citas' => ['nombre' => '02. Citas', 'icon' => 'calendar-clock'],
                'triaje' => ['nombre' => '03. Triaje', 'icon' => 'stethoscope'],
                'consulta_medicina' => ['nombre' => '04. Consulta Externa: Medicina', 'icon' => 'user-cog'],
                'consulta_odontologia' => ['nombre' => '05. Consulta Externa: Odontología', 'icon' => 'smile'],
                'consulta_nutricion' => ['nombre' => '06. Consulta Externa: Nutrición', 'icon' => 'apple'],
                'consulta_psicologia' => ['nombre' => '07. Consulta Externa: Psicología', 'icon' => 'brain'],
                'cred' => ['nombre' => '08. CRED', 'icon' => 'baby'],
                'inmunizaciones' => ['nombre' => '09. Inmunizaciones', 'icon' => 'syringe'],
                'atencion_prenatal' => ['nombre' => '10. Atención Prenatal', 'icon' => 'heart-pulse'],
                'planificacion_familiar' => ['nombre' => '11. Planificación Familiar', 'icon' => 'users'],
                'parto' => ['nombre' => '12. Parto', 'icon' => 'bed'],
                'puerperio' => ['nombre' => '13. Puerperio', 'icon' => 'home'],
                'fua_electronico' => ['nombre' => '14. FUA Electrónico', 'icon' => 'file-digit'],
                'farmacia' => ['nombre' => '15. Farmacia', 'icon' => 'pill'],
                'referencias' => ['nombre' => '16. Refcon', 'icon' => 'map-pinned'],
                'laboratorio' => ['nombre' => '17. Laboratorio', 'icon' => 'test-tube-2'],
                'urgencias' => ['nombre' => '18. Urgencias y Emergencias', 'icon' => 'ambulance'],
                'infraestructura_2d' => ['nombre' => '19. Infraestructura y Croquis 2D', 'icon' => 'box'],
            ];

            $consultoriosDinamicos = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
                ->whereNotIn('modulo_nombre', ['infraestructura_2d', 'rrhh', 'config_modulos'])
                ->get();

            $moduloRrhh = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
                ->where('modulo_nombre', 'rrhh')
                ->first();

            return view('usuario.monitoreo.modulos', compact(
                'acta',
                'modulosMaster',
                'modulosGuardados',
                'modulosActivos',
                'modulosFirmados',
                'consultoriosDinamicos',
                'moduloRrhh'
            ));
        }
    }

    /**
     * NUEVO: GESTIÓN DE SUB-MÓDULOS DE SALUD MENTAL (NIVEL 2)
     */
    public function gestionarSaludMental($id)
    {
        $acta = CabeceraMonitoreo::with(['establecimiento', 'equipo'])->findOrFail($id);

        // Recalcular estados para esta vista
        $modulosGuardados = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', '!=', 'config_modulos')
            ->pluck('modulo_nombre')->toArray();

        $modulosFirmados = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->whereNotNull('pdf_firmado_path')
            ->pluck('modulo_nombre')->toArray();

        $config = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', 'config_modulos')->first();
        $modulosActivos = $config ? $config->contenido : [];

        // LISTA DE SUB-MÓDULOS (4.1 - 4.7)
        $modulosSaludMental = [
            'sm_medicina_general' => ['nombre' => '4.1. MEDICINA GENERAL', 'icon' => 'stethoscope'],
            'sm_psiquiatria' => ['nombre' => '4.2. PSIQUIATRIA', 'icon' => 'user-cog'],
            'sm_med_familiar' => ['nombre' => '4.3. MED. FAMILIAR Y COMUNITARIA', 'icon' => 'users'],
            'sm_psicologia' => ['nombre' => '4.4. PSICOLOGIA', 'icon' => 'brain'],
            'sm_enfermeria' => ['nombre' => '4.5. ENFERMERIA', 'icon' => 'activity'],
            'sm_servicio_social' => ['nombre' => '4.6. SERVICIO SOCIAL', 'icon' => 'heart-handshake'],
            'sm_terapias' => ['nombre' => '4.7. TERAPIA LENGUAJE / OCUPACIONAL', 'icon' => 'puzzle'],
        ];

        // Apuntamos a la carpeta correcta donde creaste el archivo de submodulos
        return view('usuario.monitoreo.modulos_especializados.submodulos', compact(
            'acta',
            'modulosSaludMental',
            'modulosGuardados',
            'modulosActivos',
            'modulosFirmados'
        ));
    }

    public function toggleModulos(Request $request, $id)
    {
        try {
            MonitoreoModulos::updateOrCreate(
                ['cabecera_monitoreo_id' => $id, 'modulo_nombre' => 'config_modulos'],
                ['contenido' => $request->modulos_activos]
            );
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $monitoreo = CabeceraMonitoreo::with(['establecimiento', 'equipo', 'user'])->findOrFail($id);
        $detalles = MonitoreoModulos::where('cabecera_monitoreo_id', $id)->where('modulo_nombre', '!=', 'config_modulos')->get();
        return view('usuario.monitoreo.show', compact('monitoreo', 'detalles'));
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $monitoreo = CabeceraMonitoreo::findOrFail($id);

            if ($monitoreo->foto1)
                Storage::disk('public')->delete($monitoreo->foto1);
            if ($monitoreo->foto2)
                Storage::disk('public')->delete($monitoreo->foto2);

            $modulos = MonitoreoModulos::where('cabecera_monitoreo_id', $id)->get();
            foreach ($modulos as $m) {
                if ($m->pdf_firmado_path)
                    Storage::disk('public')->delete($m->pdf_firmado_path);
                if (isset($m->contenido['foto_evidencia']))
                    Storage::disk('public')->delete($m->contenido['foto_evidencia']);
            }

            if ($monitoreo->firmado_pdf)
                Storage::disk('public')->delete($monitoreo->firmado_pdf);
            $monitoreo->delete();
            DB::commit();
            return redirect()->route('usuario.monitoreo.index')->with('success', 'Acta eliminada correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function generarPDF($id)
    {
        $acta = CabeceraMonitoreo::with(['establecimiento', 'user', 'equipo'])->findOrFail($id);
        
        // Obtenemos los módulos y los renombramos a $modulos para que coincidan con la vista consolidado_pdf
        $modulos = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
            ->where('modulo_nombre', '!=', 'config_modulos')
            ->get();

        $prefijo = $acta->tipo_origen === 'ESPECIALIZADA' ? 'ACTA_CSMC_' : 'ACTA_IPRESS_';
        $numero = str_pad($acta->numero_acta ?? $acta->id, 5, '0', STR_PAD_LEFT);

        // Usamos la vista real: consolidado_pdf con opciones DomPDF habilitadas
        $pdf = Pdf::setOptions([
            'isPhpEnabled'         => true,
            'isRemoteEnabled'      => true,
            'isHtml5ParserEnabled' => true,
        ])->loadView('usuario.monitoreo.pdf.consolidado_pdf', [
            'acta'    => $acta,
            'modulos' => $modulos,
            'monitor' => [
                'nombre' => $acta->user ? mb_strtoupper("{$acta->user->apellido_paterno} {$acta->user->apellido_materno} {$acta->user->name}", 'UTF-8') : 'N/A'
            ],
            'jefe' => [
                'nombre' => mb_strtoupper($acta->responsable ?? 'N/A', 'UTF-8')
            ],
            'equipoMonitoreo' => $acta->equipo,
            'equipos'         => \App\Models\EquipoComputo::where('cabecera_monitoreo_id', $id)->get()
        ]);

        $pdf->setPaper('a4', 'portrait');

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $prefijo . $numero . '.pdf"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0',
            'Pragma'              => 'no-cache',
            'Expires'             => 'Sun, 02 Jan 1990 00:00:00 GMT',
        ]);
    }

    public function subirPDF(Request $request, $id)
    {
        try {
            $request->validate(['pdf_firmado' => 'required|mimes:pdf|max:10240']);

            $monitoreo = CabeceraMonitoreo::findOrFail($id);

            if ($request->hasFile('pdf_firmado')) {
                if ($monitoreo->firmado_pdf)
                    Storage::disk('public')->delete($monitoreo->firmado_pdf);

                $path = $request->file('pdf_firmado')->store('monitoreos_firmados/consolidados', 'public');

                $monitoreo->update([
                    'firmado_pdf' => $path,
                    'firmado' => true
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Acta consolidada cargada con éxito.'
                    ]);
                }
            }

            return back()->with('success', 'Acta consolidada cargada con éxito.');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Devuelve los correos del equipo de monitoreo registrados en mon_profesionales.
     */
    public function getEquipoEmails($id)
    {
        $monitoreo = CabeceraMonitoreo::with(['establecimiento', 'equipo', 'detalles'])->findOrFail($id);

        // 1. Recolectar correos del equipo de monitoreo (desde mon_profesionales)
        $docs = $monitoreo->equipo->pluck('doc')->filter()->unique();
        $emailsTeam = \App\Models\Profesional::whereIn('doc', $docs)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->pluck('email')
            ->unique()
            ->toArray();

        // 2. Recolectar correos dentro de los módulos (JSON contenido)
        $emailsModules = [];
        foreach ($monitoreo->detalles as $detalle) {
            if ($detalle->modulo_nombre === 'config_modulos') continue;
            
            $c = $detalle->contenido;
            if (!$c || !is_array($c)) continue;

            // Arreglo de llaves donde solemos guardar datos del profesional
            $keysToSearch = ['personal', 'profesional', 'rrhh', 'entrevistado'];
            
            // Búsqueda en primer nivel
            if (!empty($c['personal_correo'])) $emailsModules[] = $c['personal_correo'];
            if (!empty($c['email'])) $emailsModules[] = $c['email'];
            if (!empty($c['correo'])) $emailsModules[] = $c['correo'];

            // Búsqueda en segundo nivel (objetos comunes)
            foreach ($keysToSearch as $key) {
                if (!empty($c[$key]) && is_array($c[$key])) {
                    if (!empty($c[$key]['email'])) $emailsModules[] = $c[$key]['email'];
                    if (!empty($c[$key]['correo'])) $emailsModules[] = $c[$key]['correo'];
                    // Algunos usan 'personal_correo' dentro también por error o legado
                    if (!empty($c[$key]['personal_correo'])) $emailsModules[] = $c[$key]['personal_correo'];
                }
            }
        }

        // 3. Unificar, limpiar y validar
        $finalEmails = collect($emailsModules)
            ->map(fn($e) => trim(strtolower($e)))
            ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values();

        return response()->json([
            'emails'          => $finalEmails,
            'establecimiento' => $monitoreo->establecimiento->nombre ?? 'N/A',
            'fecha'           => Carbon::parse($monitoreo->fecha)->format('d/m/Y'),
            'numero'          => str_pad($monitoreo->numero_acta ?? $monitoreo->id, 5, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * Envía el acta consolidada firmada por correo.
     */
    public function enviarCorreo(Request $request, $id)
    {
        try {
            $request->validate(['correos' => 'required|string']);

            $monitoreo = CabeceraMonitoreo::with(['establecimiento'])->findOrFail($id);

            if (!$monitoreo->firmado_pdf || !Storage::disk('public')->exists($monitoreo->firmado_pdf)) {
                return response()->json(['success' => false, 'message' => 'El acta no tiene un PDF firmado subido.'], 400);
            }

            // Procesar correos (tag chips + texto manual)
            $rawEmails = preg_split('/[,;\s]+/', $request->correos);
            $emails = collect($rawEmails)
                ->map(fn($e) => trim(strtolower($e)))
                ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
                ->unique();

            if ($emails->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No se ingresaron correos válidos.'], 422);
            }

            $enviados = 0;
            $errores  = 0;

            foreach ($emails as $email) {
                try {
                    Log::info("📨 Enviando acta monitoreo #{$id} a {$email}");
                    Mail::to($email)->send(new ActaMonitoreoMail($monitoreo));
                    $enviados++;
                } catch (\Throwable $mailEx) {
                    Log::warning('⚠️ No se pudo enviar correo de acta de monitoreo', [
                        'error' => $mailEx->getMessage(),
                        'acta'  => $id,
                        'email' => $email,
                    ]);
                    $errores++;
                }
            }

            if ($enviados > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "✅ Se enviaron {$enviados} correo(s) exitosamente." . ($errores > 0 ? " ({$errores} fallaron)" : ''),
                ]);
            }

            return response()->json(['success' => false, 'message' => 'No se pudo enviar ningún correo.'], 500);

        } catch (\Throwable $e) {
            Log::error('❌ Error crítico en MonitoreoController@enviarCorreo', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
            ]);
            return response()->json(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Devuelve JSON con las URLs de los PDFs firmados consolidados para fusión client-side.
     */
    public function consolidadoPDFExport(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', Carbon::now()->startOfYear()->format('Y-m-d'));
        $fechaFin    = $request->input('fecha_fin', Carbon::now()->format('Y-m-d'));

        $query = CabeceraMonitoreo::with(['establecimiento'])
            ->whereNotNull('firmado_pdf')
            ->where('firmado', true)
            ->where(function($q) {
                $q->where('anulado', 0)->orWhereNull('anulado');
            })
            ->whereBetween('fecha', [$fechaInicio, $fechaFin]);

        if ($request->filled('implementador')) {
            $query->where('implementador', $request->implementador);
        }
        if ($request->filled('provincia')) {
            $query->whereHas('establecimiento', fn($q) =>
                $q->where('provincia', $request->provincia));
        }
        if ($request->filled('distrito')) {
            $query->whereHas('establecimiento', fn($q) =>
                $q->where('distrito', $request->distrito));
        }
        if ($request->filled('establecimiento_id')) {
            $query->where('establecimiento_id', $request->establecimiento_id);
        }

        $actas = $query->orderBy('fecha', 'asc')->get();

        // Also count total signed (including those without file)
        $queryTotal = CabeceraMonitoreo::where('firmado', true)
            ->where(function($q) {
                $q->where('anulado', 0)->orWhereNull('anulado');
            })
            ->whereBetween('fecha', [$fechaInicio, $fechaFin]);

        if ($request->filled('implementador')) {
            $queryTotal->where('implementador', $request->implementador);
        }
        if ($request->filled('provincia')) {
            $queryTotal->whereHas('establecimiento', fn($q) =>
                $q->where('provincia', $request->provincia));
        }
        if ($request->filled('distrito')) {
            $queryTotal->whereHas('establecimiento', fn($q) =>
                $q->where('distrito', $request->distrito));
        }
        if ($request->filled('establecimiento_id')) {
            $queryTotal->where('establecimiento_id', $request->establecimiento_id);
        }

        $totalFirmadas = $queryTotal->count();

        if ($actas->isEmpty()) {
            return response()->json(['error' => 'No se encontraron actas firmadas con archivo PDF para los filtros seleccionados.'], 400);
        }

        $incluidas = [];
        $omitidas = [];

        foreach ($actas as $acta) {
            $filePath = null;
            if (!empty($acta->firmado_pdf)) {
                // Check with case-insensitive search like the view does
                $carpeta = strtolower(dirname($acta->firmado_pdf));
                $archivoBuscado = strtolower(basename($acta->firmado_pdf));
                $archivoReal = basename($acta->firmado_pdf);
                $archivosEnServidor = Storage::disk('public')->files($carpeta === '.' ? '' : $carpeta);
                
                foreach ($archivosEnServidor as $archivoFisico) {
                    if (strtolower(basename($archivoFisico)) === $archivoBuscado) {
                        $archivoReal = basename($archivoFisico);
                        break;
                    }
                }
                
                $rutaFinal = ($carpeta === '.') ? $archivoReal : $carpeta . '/' . $archivoReal;
                $fullPath = Storage::disk('public')->path($rutaFinal);
                
                if (file_exists($fullPath)) {
                    $filePath = $rutaFinal;
                } else {
                    // Fallback: try public_path
                    $path2 = public_path('storage/' . $acta->firmado_pdf);
                    if (file_exists($path2)) {
                        $filePath = $acta->firmado_pdf;
                    }
                }
            }

            if ($filePath) {
                $incluidas[] = ['acta' => $acta, 'path' => $filePath];
            } else {
                $omitidas[] = $acta;
            }
        }

        if (empty($incluidas)) {
            return response()->json(['error' => 'Ninguna de las actas firmadas tiene el archivo PDF disponible en el servidor.'], 400);
        }

        $urls = [];
        foreach ($incluidas as $item) {
            $urls[] = asset('storage/' . $item['path']);
        }

        $listaOmitidas = [];
        foreach ($omitidas as $o) {
            $numero = str_pad($o->numero_acta ?? $o->id, 5, '0', STR_PAD_LEFT);
            $estNombre = $o->establecimiento->nombre ?? 'N/A';
            $listaOmitidas[] = "Acta #{$numero} - {$estNombre}";
        }

        return response()->json([
            'success' => true,
            'urls' => $urls,
            'total' => $totalFirmadas,
            'incluidas' => count($incluidas),
            'omitidas' => count($omitidas),
            'lista_omitidas' => $listaOmitidas
        ]);
    }

    /**
     * Cambiar el autor (user_id) de un acta de monitoreo.
     */
    public function cambiarAutor($id, Request $request)
    {
        try {
            // Verificar si el usuario autenticado tiene el rol admin
            if (Auth::user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para realizar esta acción.'
                ], 403);
            }

            // Validar la solicitud
            $request->validate([
                'user_id' => 'required|exists:users,id'
            ], [
                'user_id.required' => 'El usuario es obligatorio.',
                'user_id.exists' => 'El usuario seleccionado no existe.'
            ]);

            // Buscar la cabecera del monitoreo
            $monitoreo = CabeceraMonitoreo::findOrFail($id);

            // Verificar si el nuevo usuario tiene estado activo
            $nuevoAutor = User::findOrFail($request->user_id);
            if ($nuevoAutor->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede asignar el acta a un usuario inactivo.'
                ], 422);
            }

            // Validar que el usuario seleccionado esté dentro de la lista de DNI permitida
            $permitidosDNI = ['70314306', '70398441', '71883059', '70073797', '72762954'];
            if (!in_array($nuevoAutor->username, $permitidosDNI)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario seleccionado no está autorizado para ser asignado como autor.'
                ], 422);
            }

            // Cambiar autor
            $monitoreo->user_id = $request->user_id;
            $monitoreo->save();

            // Formatear nombre completo para la respuesta
            $nuevoAutorNombre = mb_strtoupper("{$nuevoAutor->apellido_paterno} {$nuevoAutor->apellido_materno} {$nuevoAutor->name}", 'UTF-8');

            return response()->json([
                'success' => true,
                'message' => 'El autor del acta se ha cambiado correctamente.',
                'nuevo_autor' => $nuevoAutorNombre
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Throwable $e) {
            Log::error('❌ Error al cambiar autor del acta', [
                'monitoreo_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar: ' . $e->getMessage()
            ], 500);
        }
    }
}