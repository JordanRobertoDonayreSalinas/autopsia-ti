<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Establecimiento;

class EstablecimientoController extends Controller
{
    public function buscar(Request $request)
    {
        $term = $request->get('term');

        // 1. Validación básica: Si no hay término, devolver array vacío
        if (!$term) {
            return response()->json([]);
        }

        $establecimientos = Establecimiento::query()
            ->where(function($query) use ($term) {
                $query->where('codigo', 'LIKE', "%{$term}%")
                      ->orWhere('nombre', 'LIKE', "%{$term}%");
            })
            // 2. MEJORA: Ordenar alfabéticamente ayuda visualmente al usuario
            ->orderBy('nombre', 'asc')
            ->limit(10)
            ->get([
                'id', 'codigo', 'nombre', 'provincia', 'distrito', 
                'categoria', 'red', 'microred', 'responsable'
            ]);

        $resultados = $establecimientos->map(function ($e) {
            return [
                'id'          => $e->id,
                // 'label': Lo que se ve en la lista desplegable
                'label'       => $e->codigo . ' - ' . $e->nombre, 
                // 'value': Lo que se escribe en el input al seleccionar (A veces es mejor solo el nombre)
                'value'       => $e->nombre, 
                // Datos extra para rellenar otros inputs automáticamente
                'provincia'   => $e->provincia ?? '',
                'distrito'    => $e->distrito ?? '',
                'categoria'   => $e->categoria ?? '',
                'red'         => $e->red ?? '',
                'microred'    => $e->microred ?? '',
                'responsable' => $e->responsable ?? '',
            ];
        });

        return response()->json($resultados);
    }

    public function updateCoordenadas(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'latitud'  => 'required|numeric|between:-90,90',
                'longitud' => 'required|numeric|between:-180,180',
            ]);

            $establecimiento = Establecimiento::findOrFail($id);
            $establecimiento->update([
                'latitud'  => round($validated['latitud'], 8),
                'longitud' => round($validated['longitud'], 8),
            ]);

            return response()->json([
                'ok'       => true,
                'latitud'  => $establecimiento->latitud,
                'longitud' => $establecimiento->longitud,
                'mensaje'  => 'Coordenadas actualizadas correctamente.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok'      => false,
                'mensaje' => implode(' ', array_merge(...array_values($e->errors()))),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Error al guardar: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function ajaxGetDistritos(Request $request)
    {
        $distritos = Establecimiento::query()
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
        $establecimientos = Establecimiento::query()
            ->when($request->provincia, fn($q) => $q->where('provincia', $request->provincia))
            ->when($request->distrito, fn($q) => $q->where('distrito', $request->distrito))
            ->orderBy('nombre')
            ->get(['codigo', 'nombre']);

        return response()->json($establecimientos);
    }

    public function index(Request $request)
    {
        $query = Establecimiento::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                  ->orWhere('nombre', 'like', "%{$search}%")
                  ->orWhere('responsable', 'like', "%{$search}%")
                  ->orWhere('numero_documento', 'like', "%{$search}%");
            });
        }

        if ($request->filled('provincia')) {
            $query->where('provincia', $request->provincia);
        }

        if ($request->filled('distrito')) {
            $query->where('distrito', $request->distrito);
        }

        $establecimientos = $query->orderBy('nombre', 'asc')->paginate(10)->appends($request->query());

        // Provincias y Distritos únicos para los filtros
        $provincias = Establecimiento::select('provincia')
            ->whereNotNull('provincia')
            ->where('provincia', '!=', '')
            ->distinct()
            ->orderBy('provincia')
            ->pluck('provincia');

        $distritosQuery = Establecimiento::select('distrito')
            ->whereNotNull('distrito')
            ->where('distrito', '!=', '')
            ->distinct()
            ->orderBy('distrito');

        if ($request->filled('provincia')) {
            $distritosQuery->where('provincia', $request->provincia);
        }

        $distritos = $distritosQuery->pluck('distrito');

        // Establecimientos filtrados por provincia/distrito seleccionados para el desplegable inicial
        $todosEstablecimientosQuery = Establecimiento::query();
        if ($request->filled('provincia')) {
            $todosEstablecimientosQuery->where('provincia', $request->provincia);
        }
        if ($request->filled('distrito')) {
            $todosEstablecimientosQuery->where('distrito', $request->distrito);
        }
        $todosEstablecimientos = $todosEstablecimientosQuery->orderBy('nombre', 'asc')->get(['codigo', 'nombre']);

        return view('usuario.establecimientos.index', compact('establecimientos', 'provincias', 'distritos', 'todosEstablecimientos'));
    }

    public function edit($id)
    {
        $establecimiento = Establecimiento::findOrFail($id);
        return view('usuario.establecimientos.edit', compact('establecimiento'));
    }

    public function consultarRenipress($id, \App\Services\RenipressService $renipressService)
    {
        try {
            $establecimiento = Establecimiento::findOrFail($id);
            $data = $renipressService->getDatosLimpios($establecimiento->codigo);

            if (!$data) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'El servicio de SUSALUD se encuentra temporalmente inactivo o el código es inválido.'
                ], 404);
            }

            return response()->json([
                'ok' => true,
                'datos' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Error al consultar RENIPRESS: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $establecimiento = Establecimiento::findOrFail($id);

        $validated = $request->validate([
            'nombre'                      => 'required|string|max:255',
            'codigo'                      => 'required|string|max:50|unique:establecimientos,codigo,' . $id,
            'institucion'                 => 'nullable|string|max:255',
            'direccion'                   => 'nullable|string',
            'departamento'                => 'nullable|string|max:100',
            'provincia'                   => 'nullable|string|max:100',
            'distrito'                    => 'nullable|string|max:100',
            'centro_poblado'              => 'nullable|string|max:150',
            'telefono'                    => 'nullable|string|max:100',
            'correo'                      => 'nullable|string|max:150',
            'red'                         => 'nullable|string|max:100',
            'microred'                    => 'nullable|string|max:100',
            'clas'                        => 'nullable|string|max:100',
            'odsis'                       => 'nullable|string|max:100',
            'responsable'                 => 'nullable|string|max:255',
            'tipo_documento'              => 'nullable|string|max:50',
            'numero_documento'            => 'nullable|string|max:50',
            'colegio_profesional'         => 'nullable|string|max:100',
            'colegiatura'                 => 'nullable|string|max:50',
            'rne'                         => 'nullable|string|max:50',
            'categoria'                   => 'nullable|string|max:50',
            'estado'                      => 'nullable|string|max:50',
            'condicion'                   => 'nullable|string|max:50',
            'latitud'                     => 'nullable|numeric|between:-90,90',
            'longitud'                    => 'nullable|numeric|between:-180,180',
            'altitud'                     => 'nullable|string|max:50',
            'fecha_creacion_resolucion'   => 'nullable|string|max:100',
            'fecha_registro'              => 'nullable|string|max:100',
            'numero_resolucion_creacion'  => 'nullable|string|max:150',
            'horario_atencion'            => 'nullable|string',
            'numero_ambientes'            => 'nullable|string|max:50',
            'numero_camas'                => 'nullable|string|max:50',
            'upss'                        => 'nullable|array',
            'ups'                         => 'nullable|array',
        ]);

        $establecimiento->update($validated);

        return redirect()->route('usuario.establecimientos.index')
            ->with('success', 'Establecimiento actualizado con éxito.');
    }
}