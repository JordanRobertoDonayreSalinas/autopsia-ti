<?php

namespace App\Http\Controllers;

use App\Models\CabeceraMonitoreo;
use App\Models\MonitoreoEquipo;
use App\Models\Establecimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EditMonitoreoController extends Controller
{
    /**
     * Muestra el formulario para editar un acta existente.
     */
    public function edit($id)
    {
        // Cargamos la cabecera con sus relaciones para evitar el problema de N+1 consultas
        $monitoreo = CabeceraMonitoreo::with(['establecimiento', 'equipo'])->findOrFail($id);
        $usuarios = \App\Models\User::orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('name')
            ->get();
        
        return view('usuario.monitoreo.edit', compact('monitoreo', 'usuarios'));
    }

    /**
     * Procesa la actualización de los datos en la base de datos, incluyendo imágenes.
     */
    public function update(Request $request, $id)
    {
        // 1. Validación estricta de datos (incluyendo validación de imágenes)
        $request->validate([
            'establecimiento_id' => 'required|exists:establecimientos,id',
            'fecha'              => 'required|date',
            'responsable'        => 'required|string|max:255',
            'categoria'          => 'nullable|string|max:50',
            'equipo'             => 'nullable|array',
            'redirect_to'        => 'nullable|string',
            'imagenes.*'         => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $monitoreo = CabeceraMonitoreo::findOrFail($id);

            // 2. ACTUALIZAR TABLA MAESTRA DE ESTABLECIMIENTOS
            $establecimiento = Establecimiento::findOrFail($request->establecimiento_id);
            $establecimiento->update([
                'responsable' => mb_strtoupper(trim($request->responsable)),
                'categoria'   => mb_strtoupper(trim($request->categoria)),
            ]);

            // 3. PROCESAR NUEVAS IMÁGENES DE EVIDENCIA
            if ($request->hasFile('imagenes')) {
                $files = $request->file('imagenes');
                
                // Procesar Foto 1
                if (isset($files[0])) {
                    // Borrar la foto anterior físicamente del storage si existe
                    if ($monitoreo->foto1) {
                        Storage::disk('public')->delete($monitoreo->foto1);
                    }
                    $nombreBase1 = "acta_{$id}_foto1_" . date('Ymd_His') . '_' . uniqid();
                    $monitoreo->foto1 = \App\Helpers\ImagenHelper::guardarComoWebp($files[0], 'evidencias', $nombreBase1, 'public');
                }

                // Procesar Foto 2
                if (isset($files[1])) {
                    // Borrar la foto anterior físicamente del storage si existe
                    if ($monitoreo->foto2) {
                        Storage::disk('public')->delete($monitoreo->foto2);
                    }
                    $nombreBase2 = "acta_{$id}_foto2_" . date('Ymd_His') . '_' . uniqid();
                    $monitoreo->foto2 = \App\Helpers\ImagenHelper::guardarComoWebp($files[1], 'evidencias', $nombreBase2, 'public');
                }
            }

            // 3.1 ABSORBER FOTOS PENDIENTES SUBIDAS DESDE EL CELULAR (QR): ya están
            // en disco (subidas por EvidenciaMovilFijoController::subir()), solo se
            // asignan a la primera casilla libre que no se acaba de llenar arriba.
            $fotosPendientesMovil = $request->input('fotos_pendientes_movil', []);
            if (is_array($fotosPendientesMovil)) {
                foreach ($fotosPendientesMovil as $pathPendiente) {
                    if (empty($pathPendiente)) {
                        continue;
                    }
                    if (empty($monitoreo->foto1)) {
                        $monitoreo->foto1 = $pathPendiente;
                    } elseif (empty($monitoreo->foto2)) {
                        $monitoreo->foto2 = $pathPendiente;
                    }
                }
            }

            // 4. ACTUALIZAR CABECERA DEL ACTA
            $monitoreo->establecimiento_id = $request->establecimiento_id;
            $monitoreo->fecha = $request->fecha;
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
            $monitoreo->save();

            // 5. SINCRONIZAR EQUIPO DE MONITOREO
            MonitoreoEquipo::where('cabecera_monitoreo_id', $id)->delete();

            if ($request->has('equipo') && is_array($request->equipo)) {
                foreach ($request->equipo as $item) {
                    if (!empty($item['doc'])) {
                        MonitoreoEquipo::create([
                            'cabecera_monitoreo_id' => $id,
                            'tipo_doc'              => $item['tipo_doc'] ?? 'DNI',
                            'doc'                   => trim($item['doc']),
                            'apellido_paterno'      => mb_strtoupper(trim($item['apellido_paterno'])),
                            'apellido_materno'      => mb_strtoupper(trim($item['apellido_materno'])),
                            'nombres'               => mb_strtoupper(trim($item['nombres'])),
                            'cargo'                 => mb_strtoupper(trim($item['cargo'])),
                            'institucion'           => mb_strtoupper(trim($item['institucion'] ?? 'DIRESA')),
                        ]);
                    }
                }
            }

            DB::commit();

            // Al guardar, se cierra el código QR de evidencia móvil activo (si lo
            // hay) para esta acta, igual que en consultorios/RR.HH.
            app(EvidenciaMovilFijoController::class)->cerrarActivo('acta', $id);

            // 6. FLUJO DE REDIRECCIÓN DINÁMICO
            if ($request->input('redirect_to') === 'modulos') {
                return redirect()->route('usuario.monitoreo.modulos', $id)
                                 ->with('success', "Cabecera e imágenes actualizadas. Ahora puede completar los módulos técnicos.");
            }

            return redirect()->route('usuario.monitoreo.index')
                             ->with('success', "El Acta #{$id} ha sido actualizada exitosamente.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error en Update Monitoreo: " . $e->getMessage());
            return back()->withErrors(['error' => 'Hubo un problema al guardar los cambios: ' . $e->getMessage()])->withInput();
        }
    }
}