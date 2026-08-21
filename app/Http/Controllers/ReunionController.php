<?php

namespace App\Http\Controllers;

use App\Models\Reunion;
use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ActasReunionesExport;

class ReunionController extends Controller
{
    public function index(Request $request)
    {
        $query = Reunion::query();

        // Filtros opcionales
        if ($request->filled('implementador')) {
            $term = strtolower($request->implementador);
            $parts = explode(' ', $term);

            $query->where(function($q) use ($parts) {
                foreach ($parts as $part) {
                    $part = trim($part);
                    if ($part) {
                        $q->whereRaw('LOWER(participantes) LIKE ?', ['%' . $part . '%']);
                    }
                }
            });
        }
        // Fechas por defecto: primer día del año actual y hoy
        $fechaDesdeDefault = \Carbon\Carbon::now()->startOfYear()->format('Y-m-d');
        $fechaHastaDefault = \Carbon\Carbon::now()->format('Y-m-d');

        $valDesde = $request->input('fecha_desde', $fechaDesdeDefault);
        $valHasta = $request->input('fecha_hasta', $fechaHastaDefault);

        if ($valDesde) {
            $query->whereDate('fecha_reunion', '>=', $valDesde);
        }
        if ($valHasta) {
            $query->whereDate('fecha_reunion', '<=', $valHasta);
        }
        if ($request->filled('firmado')) {
            $query->where('firmado', $request->firmado);
        }
        
        // Filtro de visibilidad (anulado)
        $estado_anulado = $request->input('estado_anulado', 'todos');
        if ($estado_anulado === 'activo') {
            $query->where('anulado', false);
        } elseif ($estado_anulado === 'anulado') {
            $query->where('anulado', true);
        }

        $reuniones = $query->orderBy('fecha_reunion', 'desc')->paginate(15);
        
        // Métricas para el banner superior
        $total_reuniones = Reunion::count();
        $countFirmadas = Reunion::where('firmado', true)->where('anulado', false)->count();
        $countPendientes = Reunion::where('firmado', false)->where('anulado', false)->count();
        $countAnuladas = Reunion::where('anulado', true)->count();

        // Obtener implementadores únicos de la columna JSON 'participantes'
        $implementadoresUnicos = collect();
        Reunion::query()->pluck('participantes')->each(function ($participantes) use (&$implementadoresUnicos) {
            if (is_array($participantes)) {
                foreach ($participantes as $p) {
                    // Consideramos a todos los participantes o solo los de cargo IMPLEMENTADOR.
                    // Para ser seguros, si su cargo es implementador o simplemente agregamos todos los que están.
                    // Mejor los que tienen cargo IMPLEMENTADOR:
                    if (isset($p['cargo']) && mb_strtoupper(trim($p['cargo']), 'UTF-8') === 'IMPLEMENTADOR') {
                        // El formato suele ser NOMBRES APELLIDOS o APELLIDOS NOMBRES.
                        $label = mb_strtoupper(trim(($p['apellidos'] ?? '') . ' ' . ($p['nombres'] ?? '')), 'UTF-8');
                        // Limpiar espacios en blanco adicionales
                        $label = preg_replace('/\s+/', ' ', $label);
                        if ($label) {
                            $implementadoresUnicos->push($label);
                        }
                    }
                }
            }
        });
        $implementadores = $implementadoresUnicos->filter()->unique()->sort()->values();

        return view('usuario.reuniones.index', compact(
            'reuniones', 
            'total_reuniones', 
            'countFirmadas', 
            'countPendientes', 
            'countAnuladas',
            'valDesde',
            'valHasta',
            'implementadores'
        ));
    }

    public function create()
    {
        $usuarioActual = auth()->user();

        // Buscar teléfono del usuario en mon_profesionales por su DNI
        $dniCreador = $usuarioActual->documento ?? $usuarioActual->username ?? null;
        $profesional = $dniCreador
            ? Profesional::where('doc', $dniCreador)->first()
            : null;

        $celularCreador    = $profesional->telefono ?? null;
        $cargoDefault      = 'IMPLEMENTADOR';
        $institucionDefault = 'CONSORCIO TRANSFORMACION DIGITAL';

        return view('usuario.reuniones.create', [
            'reunion'           => new Reunion(),
            'usuarioActual'     => $usuarioActual,
            'celularCreador'    => $celularCreador,
            'cargoDefault'      => $cargoDefault,
            'institucionDefault' => $institucionDefault,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo_reunion' => 'required|string|max:255',
            'fecha_reunion' => 'required|date',
            'hora_reunion' => 'required',
            'nombre_institucion' => 'required|string|max:255',
            'descripcion_general' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only([
                'titulo_reunion', 'fecha_reunion', 'hora_reunion', 'hora_finalizada_reunion',
                'nombre_institucion', 'descripcion_general'
            ]);

            // Convertir a mayúsculas
            $data['titulo_reunion'] = mb_strtoupper($data['titulo_reunion'], 'UTF-8');
            $data['nombre_institucion'] = mb_strtoupper($data['nombre_institucion'], 'UTF-8');

            // Arrays dinámicos
            $data['acuerdos'] = $request->input('acuerdos', []);
            $data['comentarios_observaciones'] = $request->input('observaciones', []); // form says observaciones
            
            $participantes = $request->input('participantes', []);
            foreach ($participantes as &$p) {
                if (isset($p['apellidos'])) $p['apellidos'] = mb_strtoupper($p['apellidos'], 'UTF-8');
                if (isset($p['nombres'])) $p['nombres'] = mb_strtoupper($p['nombres'], 'UTF-8');
                if (isset($p['cargo'])) $p['cargo'] = mb_strtoupper($p['cargo'], 'UTF-8');
                if (isset($p['institucion'])) $p['institucion'] = mb_strtoupper($p['institucion'], 'UTF-8');
            }
            $data['participantes'] = $participantes;
            $data['anulado'] = false;

            $reunion = Reunion::create($data);

            // Manejar imágenes si las hay
            if ($request->hasFile('imagenes')) {
                $files = $request->file('imagenes');
                $updates = [];
                for ($i = 0; $i < min(2, count($files)); $i++) {
                    $file = $files[$i];
                    $path = $file->store('reuniones', 'public');
                    // Retener la ruta relativa para la DB incluyendo 'storage/'
                    $updates['foto_' . ($i + 1)] = 'storage/' . $path;
                }
                
                if (!empty($updates)) {
                    $reunion->update($updates);
                }
            }

            DB::commit();
            return redirect()->route('usuario.reuniones.index')->with('success', 'Acta de reunión creada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Ocurrió un error al guardar: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $reunion = Reunion::findOrFail($id);
        if ($reunion->anulado) {
            return redirect()->route('usuario.reuniones.index')->with('error', 'No se puede editar un acta anulada.');
        }

        return view('usuario.reuniones.edit', compact('reunion'));
    }

    public function update(Request $request, $id)
    {
        $reunion = Reunion::findOrFail($id);

        if ($reunion->anulado) {
            return redirect()->route('usuario.reuniones.index')->with('error', 'No se puede editar un acta anulada.');
        }

        $request->validate([
            'titulo_reunion' => 'required|string|max:255',
            'fecha_reunion' => 'required|date',
            'hora_reunion' => 'required',
            'nombre_institucion' => 'required|string|max:255',
            'descripcion_general' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only([
                'titulo_reunion', 'fecha_reunion', 'hora_reunion', 'hora_finalizada_reunion',
                'nombre_institucion', 'descripcion_general'
            ]);

            $data['titulo_reunion'] = mb_strtoupper($data['titulo_reunion'], 'UTF-8');
            $data['nombre_institucion'] = mb_strtoupper($data['nombre_institucion'], 'UTF-8');

            $data['acuerdos'] = $request->input('acuerdos', []);
            $data['comentarios_observaciones'] = $request->input('observaciones', []);
            
            $participantes = $request->input('participantes', []);
            foreach ($participantes as &$p) {
                if (isset($p['apellidos'])) $p['apellidos'] = mb_strtoupper($p['apellidos'], 'UTF-8');
                if (isset($p['nombres'])) $p['nombres'] = mb_strtoupper($p['nombres'], 'UTF-8');
                if (isset($p['cargo'])) $p['cargo'] = mb_strtoupper($p['cargo'], 'UTF-8');
                if (isset($p['institucion'])) $p['institucion'] = mb_strtoupper($p['institucion'], 'UTF-8');
            }
            $data['participantes'] = $participantes;

            // Eliminar fotos si se solicita
            if ($request->input('quitar_foto_1') == '1' && $reunion->foto_1) {
                // Eliminar archivo
                $filePath = str_replace('storage/', '', $reunion->foto_1); // Queda 'reuniones/xyz.jpg'
                Storage::disk('public')->delete($filePath);
                $data['foto_1'] = null;
            }
            if ($request->input('quitar_foto_2') == '1' && $reunion->foto_2) {
                $filePath = str_replace('storage/', '', $reunion->foto_2);
                Storage::disk('public')->delete($filePath);
                $data['foto_2'] = null;
            }

            // Actualizar imágenes nuevas
            if ($request->hasFile('imagenes')) {
                $files = $request->file('imagenes');
                $indice_libre = 1;
                foreach($files as $file) {
                    if ($indice_libre > 2) break; // max 2
                    
                    // Buscar hueco (si foto_1 esta vacia o se la acaba de vaciar)
                    if (empty($data['foto_1']) && empty($reunion->foto_1)) {
                        $path = $file->store('reuniones', 'public');
                        $data['foto_1'] = 'storage/' . $path;
                    } else if (empty($data['foto_2']) && empty($reunion->foto_2)) {
                        $path = $file->store('reuniones', 'public');
                        $data['foto_2'] = 'storage/' . $path;
                    } else if (isset($data['foto_1']) && empty($data['foto_1'])) {
                        // Si decidio quitar la foto 1, podemos llenarlo aqui
                        $path = $file->store('reuniones', 'public');
                        $data['foto_1'] = 'storage/' . $path;
                    } else if (isset($data['foto_2']) && empty($data['foto_2'])) {
                        $path = $file->store('reuniones', 'public');
                        $data['foto_2'] = 'storage/' . $path;
                    } else {
                        // Si ambas estan llenas y manda nuevas, podemos omitir o sobreescribir. Sobreescribamos la 1 y 2
                        if ($indice_libre == 1) {
                            if (!empty($reunion->foto_1)) {
                                Storage::disk('public')->delete(str_replace('storage/', '', $reunion->foto_1));
                            }
                            $path = $file->store('reuniones', 'public');
                            $data['foto_1'] = 'storage/' . $path;
                        } else {
                            if (!empty($reunion->foto_2)) {
                                Storage::disk('public')->delete(str_replace('storage/', '', $reunion->foto_2));
                            }
                            $path = $file->store('reuniones', 'public');
                            $data['foto_2'] = 'storage/' . $path;
                        }
                    }
                    $indice_libre++;
                }
            }

            // Absorber fotos pendientes subidas desde el celular (QR): ya están en
            // disco (subidas por EvidenciaMovilFijoController::subir()), solo se
            // asignan a la primera casilla libre que no se acaba de llenar arriba.
            $fotosPendientesMovil = $request->input('fotos_pendientes_movil', []);
            if (is_array($fotosPendientesMovil)) {
                foreach ($fotosPendientesMovil as $pathPendiente) {
                    if (empty($pathPendiente)) {
                        continue;
                    }
                    $valorGuardado = 'storage/' . $pathPendiente;
                    if (empty($data['foto_1']) && empty($reunion->foto_1)) {
                        $data['foto_1'] = $valorGuardado;
                    } elseif (empty($data['foto_2']) && empty($reunion->foto_2)) {
                        $data['foto_2'] = $valorGuardado;
                    } elseif (isset($data['foto_1']) && empty($data['foto_1'])) {
                        $data['foto_1'] = $valorGuardado;
                    } elseif (isset($data['foto_2']) && empty($data['foto_2'])) {
                        $data['foto_2'] = $valorGuardado;
                    }
                }
            }

            $reunion->update($data);

            DB::commit();

            // Al guardar, se cierra el código QR de evidencia móvil activo (si lo
            // hay) para esta acta de reunión.
            app(EvidenciaMovilFijoController::class)->cerrarActivo('reunion', $id);

            return redirect()->route('usuario.reuniones.index')->with('success', 'Acta de reunión actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Ocurrió un error al actualizar: ' . $e->getMessage());
        }
    }

    public function anular($id)
    {
        try {
            $reunion = Reunion::findOrFail($id);
            $reunion->anulado = !$reunion->anulado; // Toggle
            $reunion->save();

            $mensaje = $reunion->anulado ? 'Acta anulada correctamente.' : 'Acta reactivada correctamente.';
            return response()->json(['success' => true, 'message' => $mensaje, 'estado' => $reunion->anulado]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function pdf($id)
    {
        $reunion = Reunion::findOrFail($id);
        
        $pdf = Pdf::loadView('usuario.reuniones.pdf', compact('reunion'))
                  ->setOptions(['isRemoteEnabled' => true, 'isPhpEnabled' => true])
                  ->setPaper('a4', 'portrait');
                  
        $titulo = mb_strtoupper($reunion->titulo_reunion, 'UTF-8');
        $correlativo = str_pad($reunion->id, 3, '0', STR_PAD_LEFT);
        return $pdf->stream("ACTA DE REUNION Nº {$correlativo} - {$titulo}.pdf");
    }

    public function subirPDF(Request $request, $id)
    {
        $request->validate(['pdf_firmado' => 'required|mimes:pdf|max:20480']);
        $reunion = Reunion::findOrFail($id);
        
        if ($reunion->archivo_pdf && Storage::disk('public')->exists($reunion->archivo_pdf)) {
            Storage::disk('public')->delete($reunion->archivo_pdf);
        }
        
        $path = $request->file('pdf_firmado')->store('reuniones_firmadas', 'public');
        $reunion->update(['archivo_pdf' => $path, 'firmado' => true]);
        
        return redirect()->back()->with('success', '✅ El acta firmada fue subida correctamente.');
    }

    public function exportarExcel(Request $request)
    {
        $query = Reunion::query();

        if ($request->filled('titulo')) {
            $query->where('titulo_reunion', 'LIKE', '%' . $request->titulo . '%');
        }
        if ($request->filled('institucion')) {
            $query->where('nombre_institucion', 'LIKE', '%' . $request->institucion . '%');
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_reunion', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_reunion', '<=', $request->fecha_hasta);
        }
        if ($request->filled('firmado')) {
            $query->where('firmado', $request->firmado);
        }
        
        $estado_anulado = $request->input('estado_anulado', 'todos');
        if ($estado_anulado === 'activo') {
            $query->where('anulado', false);
        } elseif ($estado_anulado === 'anulado') {
            $query->where('anulado', true);
        }

        $reuniones = $query->orderBy('fecha_reunion', 'desc')->get();

        $filename = 'Actas_Reuniones_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new ActasReunionesExport($reuniones), $filename);
    }

    /**
     * Devuelve JSON con las URLs de los PDFs firmados para fusión client-side.
     */
    public function consolidadoPDFExport(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', \Carbon\Carbon::now()->startOfYear()->format('Y-m-d'));
        $fechaHasta = $request->input('fecha_hasta', \Carbon\Carbon::now()->format('Y-m-d'));

        $query = Reunion::whereNotNull('archivo_pdf')
            ->where('firmado', true)
            ->where('anulado', false)
            ->whereDate('fecha_reunion', '>=', $fechaDesde)
            ->whereDate('fecha_reunion', '<=', $fechaHasta);

        if ($request->filled('titulo')) {
            $query->where('titulo_reunion', 'LIKE', '%' . $request->titulo . '%');
        }
        if ($request->filled('institucion')) {
            $query->where('nombre_institucion', 'LIKE', '%' . $request->institucion . '%');
        }

        $actas = $query->orderBy('fecha_reunion', 'asc')->get();

        // Count total firmadas (including those without file)
        $queryTotal = Reunion::where('firmado', true)
            ->where('anulado', false)
            ->whereDate('fecha_reunion', '>=', $fechaDesde)
            ->whereDate('fecha_reunion', '<=', $fechaHasta);

        if ($request->filled('titulo')) {
            $queryTotal->where('titulo_reunion', 'LIKE', '%' . $request->titulo . '%');
        }
        if ($request->filled('institucion')) {
            $queryTotal->where('nombre_institucion', 'LIKE', '%' . $request->institucion . '%');
        }

        $totalFirmadas = $queryTotal->count();

        if ($actas->isEmpty()) {
            return response()->json(['error' => 'No se encontraron actas de reunión firmadas con archivo PDF para los filtros seleccionados.'], 400);
        }

        $incluidas = [];
        $omitidas = [];

        foreach ($actas as $acta) {
            $filePath = null;
            if (!empty($acta->archivo_pdf)) {
                $path1 = Storage::disk('public')->path($acta->archivo_pdf);
                if (file_exists($path1)) {
                    $filePath = $acta->archivo_pdf;
                } else {
                    $path2 = public_path('storage/' . $acta->archivo_pdf);
                    if (file_exists($path2)) {
                        $filePath = $acta->archivo_pdf;
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
            $correlativo = str_pad($o->id, 4, '0', STR_PAD_LEFT);
            $listaOmitidas[] = "Acta #{$correlativo} - {$o->titulo_reunion}";
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
}
