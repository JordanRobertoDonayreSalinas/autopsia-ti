<?php

namespace App\Http\Controllers;

use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SignatureBankController extends Controller
{
    /**
     * Muestra el listado de profesionales y sus firmas (Banco de Firmas).
     */
    public function index(Request $request)
    {
        $query = Profesional::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('doc', 'like', "%{$search}%")
                  ->orWhere('nombres', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('apellido_materno', 'like', "%{$search}%");
            });
        }

        $profesionales = $query->orderBy('apellido_paterno')->paginate(15);

        return view('admin.firmas.index', compact('profesionales'));
    }

    /**
     * Sube o actualiza la firma de un profesional.
     */
    public function store(Request $request)
    {
        $request->validate([
            'doc' => 'required|exists:mon_profesionales,doc',
            'firma' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        try {
            $profesional = Profesional::where('doc', $request->doc)->firstOrFail();

            if ($request->hasFile('firma')) {
                // Eliminar anterior
                if ($profesional->firma_path && Storage::disk('public')->exists($profesional->firma_path)) {
                    Storage::disk('public')->delete($profesional->firma_path);
                }

                $path = $request->file('firma')->store('firmas_profesionales', 'public');
                
                $profesional->update([
                    'firma_path' => $path,
                    'tipo_firma' => 'imagen_escaneada',
                    'ultima_actualizacion_firma' => now()
                ]);
            }

            return back()->with('success', 'Firma actualizada correctamente para ' . $profesional->nombres);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al subir la firma: ' . $e->getMessage());
        }
    }

    /**
     * Elimina la firma de un profesional.
     */
    public function destroy($doc)
    {
        try {
            $profesional = Profesional::where('doc', $doc)->firstOrFail();

            if ($profesional->firma_path && Storage::disk('public')->exists($profesional->firma_path)) {
                Storage::disk('public')->delete($profesional->firma_path);
            }

            $profesional->update([
                'firma_path' => null,
                'ultima_actualizacion_firma' => null
            ]);

            return back()->with('success', 'Firma eliminada correctamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar la firma.');
        }
    }
}
