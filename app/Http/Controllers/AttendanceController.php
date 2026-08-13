<?php

namespace App\Http\Controllers;

use App\Models\Reunion;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Muestra el formulario de asistencia para una reunión específica.
     */
    public function show($id)
    {
        $reunion = Reunion::findOrFail($id);
        
        // Verificar si la asistencia está activa y dentro del rango de tiempo
        $now = Carbon::now();
        $isOpen = $reunion->asistencia_desde && $reunion->asistencia_hasta && 
                   $now->between($reunion->asistencia_desde, $reunion->asistencia_hasta);

        if (!$isOpen) {
            return view('public.attendance.closed', compact('reunion'));
        }

        return view('public.attendance.form', compact('reunion'));
    }

    /**
     * Guarda la asistencia de un participante.
     */
    public function store(Request $request, $id)
    {
        $reunion = Reunion::findOrFail($id);

        // Verificar si todavía está abierto
        $now = Carbon::now();
        $isOpen = $reunion->asistencia_desde && $reunion->asistencia_hasta && 
                   $now->between($reunion->asistencia_desde, $reunion->asistencia_hasta);

        if (!$isOpen) {
            return back()->with('error', 'El formulario de asistencia ya no está disponible.');
        }

        $request->validate([
            'tipo_documento' => 'required|string',
            'dni' => 'required|string',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'cargo' => 'required|string|max:255',
            'institucion' => 'required|string|max:255',
            'celular' => ['nullable', 'string', 'max:20', function ($attribute, $value, $fail) {
                if ($value === '999999999') {
                    $fail('El número de celular no puede ser 999999999.');
                }
            }],
        ]);

        $nuevoParticipante = [
            'tipo_documento' => mb_strtoupper($request->tipo_documento, 'UTF-8'),
            'dni' => $request->dni,
            'nombres' => mb_strtoupper($request->nombres, 'UTF-8'),
            'apellidos' => mb_strtoupper($request->apellidos, 'UTF-8'),
            'cargo' => mb_strtoupper($request->cargo, 'UTF-8'),
            'institucion' => mb_strtoupper($request->institucion, 'UTF-8'),
            'celular' => $request->celular,
            'fecha_registro' => $now->toDateTimeString(),
        ];

        $participantes = $reunion->participantes ?? [];
        
        // Evitar duplicados por DNI en la misma reunión
        foreach ($participantes as $p) {
            if (isset($p['dni']) && $p['dni'] == $request->dni) {
                return back()->with('info', 'Usted ya se encuentra registrado en esta asistencia.');
            }
        }

        $participantes[] = $nuevoParticipante;
        $reunion->update(['participantes' => $participantes]);

        return view('public.attendance.success', compact('reunion'));
    }

    /**
     * Activa el rango de asistencia (Admin).
     */
    public function activate(Request $request, $id)
    {
        $request->validate([
            'minutos' => 'required|integer|min:1|max:1440', // Max 24 horas
        ]);

        $reunion = Reunion::findOrFail($id);
        
        $desde = Carbon::now();
        $hasta = $desde->copy()->addMinutes((int) $request->minutos);

        $reunion->update([
            'asistencia_desde' => $desde,
            'asistencia_hasta' => $hasta
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Asistencia activada por ' . $request->minutos . ' minutos.',
            'hasta' => $hasta->format('d/m/Y H:i:s')
        ]);
    }

    /**
     * Muestra una vista optimizada para proyectar el QR.
     */
    public function projectQR($id)
    {
        $reunion = Reunion::findOrFail($id);
        $url = url('/asistencia-reunion/' . $reunion->id);
        
        return view('usuario.reuniones.project_qr', compact('reunion', 'url'));
    }
}
