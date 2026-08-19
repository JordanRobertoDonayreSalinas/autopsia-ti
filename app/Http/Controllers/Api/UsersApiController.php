<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Espejo API (autenticado con Sanctum) de las acciones de escritura que
 * AdminController ya expone por sesión web en /admin/gestionar-usuarios/*.
 * Mismas reglas de validación y mismo comportamiento — ver AdminController
 * para el original. auth:sanctum solo confirma identidad; aquí además se
 * exige rol admin porque el guard por defecto (Auth::user()) no resuelve al
 * usuario del token en un request de API, así que se usa $request->user().
 */
class UsersApiController extends Controller
{
    private function ensureAdmin(Request $request): ?JsonResponse
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Acceso denegado: se requiere rol de administrador.'], 403);
        }
        return null;
    }

    public function store(Request $request): JsonResponse
    {
        if ($deny = $this->ensureAdmin($request)) return $deny;

        $request->validate([
            'name' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:admin,user,operador,visor_cronograma',
        ]);

        $user = User::create([
            'name' => $request->name,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status ?? 'active',
        ]);

        return response()->json(['success' => true, 'message' => 'Usuario creado correctamente.', 'user' => $user]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        if ($deny = $this->ensureAdmin($request)) return $deny;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
            'username' => "required|unique:users,username,{$user->id}",
            'role' => 'required|in:admin,user,operador,visor_cronograma',
            'status' => 'required|in:active,inactive',
        ]);

        $user->update($request->only('name', 'apellido_paterno', 'apellido_materno', 'email', 'username', 'role', 'status'));

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json(['success' => true, 'message' => 'Usuario actualizado correctamente.', 'user' => $user]);
    }

    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        if ($deny = $this->ensureAdmin($request)) return $deny;

        if ($user->id === $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'No puedes bloquear tu propia cuenta.'], 403);
        }

        $user->status = ($user->status === 'active') ? 'inactive' : 'active';
        $user->save();

        $msg = ($user->status === 'active') ? 'Activado' : 'Bloqueado';

        return response()->json(['success' => true, 'message' => 'Usuario ' . $msg . ' correctamente.', 'status' => $user->status]);
    }

    /**
     * Idéntico a AdminController::buscarDni (ver ese método para el detalle
     * del flujo local -> RENIEC). Se duplica en vez de compartirse porque
     * uno depende de sesión web y el otro de token API.
     */
    public function buscarDni(Request $request): JsonResponse
    {
        if ($deny = $this->ensureAdmin($request)) return $deny;

        $tipoDoc = strtoupper(trim($request->input('tipo_doc', 'DNI')));
        $doc = trim($request->input('dni', ''));
        $localOnly = $request->has('local_only');

        if (empty($doc)) {
            return response()->json(['exists' => false, 'exists_external' => false]);
        }

        $usuarioExistente = User::where('username', $doc)->first();
        $existingUser = $usuarioExistente ? [
            'nombre' => trim(($usuarioExistente->apellido_paterno ?? '') . ' ' . ($usuarioExistente->name ?? '')),
            'role' => $usuarioExistente->role,
            'status' => $usuarioExistente->status,
            'id' => $usuarioExistente->id,
        ] : null;

        $profesional = \App\Models\Profesional::where('doc', $doc)->first();

        if ($profesional) {
            return response()->json([
                'exists' => true,
                'exists_external' => false,
                'existing_user' => $existingUser,
                'tipo_doc' => $profesional->tipo_doc,
                'apellido_paterno' => $profesional->apellido_paterno,
                'apellido_materno' => $profesional->apellido_materno,
                'nombres' => $profesional->nombres,
                'email' => $profesional->email ?? '',
            ]);
        }

        if ($localOnly) {
            return response()->json([
                'exists' => false,
                'exists_external' => false,
                'existing_user' => $existingUser,
            ]);
        }

        if ($tipoDoc === 'DNI' && preg_match('/^\d{8}$/', $doc)) {
            $decolecta = new \App\Services\DecolectaService();
            $result = $decolecta->consultarDni($doc);

            if (isset($result['error']) && $result['error'] === 'quota_exceeded') {
                return response()->json([
                    'exists' => false,
                    'exists_external' => false,
                    'quota_exceeded' => true,
                    'existing_user' => $existingUser,
                    'message' => 'Límite mensual de validaciones RENIEC excedido.',
                ]);
            }

            if (isset($result['success']) && $result['success']) {
                $data = $result['data'];
                return response()->json([
                    'exists' => false,
                    'exists_external' => true,
                    'existing_user' => $existingUser,
                    'tipo_doc' => 'DNI',
                    'apellido_paterno' => $data['apellido_paterno'],
                    'apellido_materno' => $data['apellido_materno'],
                    'nombres' => $data['nombres'],
                    'email' => '',
                    'remaining_tokens' => $data['remaining_tokens'] ?? null,
                ]);
            }
        }

        return response()->json([
            'exists' => false,
            'exists_external' => false,
            'existing_user' => $existingUser,
        ]);
    }
}
