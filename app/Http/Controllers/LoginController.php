<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    // 1. Mostrar el formulario
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // 2. Procesar el Login
    public function login(Request $request)
    {
        // --- VALIDACIÓN ---
        $credentials = $request->validate([
            'username' => ['required', 'string', 'size:8'],
            'password' => ['required'],
        ], [
            'username.required' => 'Por favor, ingresa tu usuario.',
            'username.size' => 'El usuario debe tener exactamente 8 dígitos.',
            'password.required' => 'Debes ingresar tu contraseña.',
        ]);

        // Agregar condición global de cuenta activa
        $credentials['status'] = 'active';

        // Intentar loguear
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // --- LÓGICA DE REDIRECCIÓN POR ROLES ---
            if ($user->role === 'admin') {
                $rutaDestino = route('admin.users.index');
            } elseif ($user->role === 'operador') {
                $rutaDestino = route('usuario.monitoreo.index');
            } elseif ($user->role === 'visor_cronograma') {
                $rutaDestino = route('usuario.reportes.cronograma');
            } else {
                $rutaDestino = route('usuario.perfil');
            }

            // --- RESPUESTA PARA AJAX (JSON) ---
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect' => $rutaDestino
                ]);
            }

            // --- RESPUESTA NORMAL ---
            return redirect()->intended($rutaDestino);
        }

        // --- SI FALLA EL LOGIN ---
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas. Inténtalo de nuevo.'
            ], 422);
        }

        return back()->withErrors([
            'username' => 'El usuario o la contraseña son incorrectos.',
        ])->onlyInput('username');
    }

    /**
     * Login de la API para la app Flutter (POST /api/v1/login).
     *
     * Antes reutilizaba login() de arriba, pensado para sesión web con
     * cookies: en éxito solo devolvía { success, redirect } sin token ni
     * datos del usuario. La app nativa no puede autenticar llamadas
     * siguientes con eso — ver Informe de revisión, sección 5.
     *
     * Emite un token de Laravel Sanctum y el usuario completo, para que
     * Flutter lo guarde y lo envíe como Authorization: Bearer <token> en
     * cada llamada protegida (hoy solo POST /v1/sync la exige).
     */
    public function apiLogin(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ], [
            'username.required' => 'Por favor, ingresa tu usuario.',
            'password.required' => 'Debes ingresar tu contraseña.',
        ]);

        $user = User::where('username', $credentials['username'])
            ->where('status', 'active')
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas. Inténtalo de nuevo.',
            ], 422);
        }

        // Un solo token vigente por dispositivo/app: se revocan los anteriores.
        $user->tokens()->where('name', 'flutter-app')->delete();
        $token = $user->createToken('flutter-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'               => $user->id,
                'name'             => $user->name,
                'apellido_paterno' => $user->apellido_paterno,
                'apellido_materno' => $user->apellido_materno,
                'nombre_completo'  => $user->full_name,
                'username'         => $user->username,
                'role'             => $user->role,
            ],
        ]);
    }

    // 3. Cerrar sesión
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}