<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Acta;
use App\Models\Establecimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Dashboard del Administrador: Estadísticas globales.
     * Vista: resources/views/admin/dashboard/dashboard.blade.php
     */
    public function index()
    {
        $totalActas = Acta::count();
        $totalUsuarios = User::count();

        // Mapeo con nombres completos en español
        $mesesEspañol = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        // Obtener actas por mes del año actual, ordenadas por mes DESC (más reciente primero)
        $actasPorMes = Acta::selectRaw('MONTH(fecha) as mes, COUNT(*) as total')
            ->whereYear('fecha', date('Y'))
            ->groupBy('mes')
            ->orderBy('mes', 'desc')
            ->get()
            ->map(function ($item) use ($mesesEspañol) {
                return [
                    'nombre_mes' => $mesesEspañol[$item->mes] ?? 'S/N',
                    'total' => $item->total
                ];
            });

        // Ranking de establecimientos con más actividad (Top 5)
        $topEstablecimientos = Establecimiento::withCount('actas')
            ->orderBy('actas_count', 'desc')
            ->take(5)
            ->get();

        // Retorno a la ruta física exacta proporcionada
        return view('admin.dashboard.dashboard', compact('totalActas', 'totalUsuarios', 'actasPorMes', 'topEstablecimientos'));
    }

    // =========================================================================
    // GESTIÓN DE USUARIOS
    // =========================================================================

    /**
     * Listado de Usuarios
     * Vista: resources/views/admin/gestionar_usuarios/index.blade.php
     */
    public function usersIndex()
    {
        $users = User::paginate(10);
        return view('admin.gestionar_usuarios.index', compact('users'));
    }

    /**
     * Formulario de creación
     * Vista: resources/views/admin/gestionar_usuarios/create.blade.php
     */
    public function usersCreate()
    {
        return view('admin.gestionar_usuarios.create');
    }

    /**
     * Almacenar nuevo usuario
     */
    public function usersStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:admin,user,operador,visor_cronograma',
        ]);

        User::create([
            'name' => $request->name,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status ?? 'active',
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Formulario de edición
     * Vista: resources/views/admin/gestionar_usuarios/edit.blade.php
     */
    public function usersEdit(User $user)
    {
        return view('admin.gestionar_usuarios.edit', compact('user'));
    }

    /**
     * Actualizar usuario existente
     */
    public function usersUpdate(Request $request, User $user)
    {
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

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Búsqueda de documento para creación de usuarios.
     * Replica el patrón de GestionAdministrativaController@buscarProfesional:
     *   - ?local_only=1  → solo consulta la tabla local mon_profesionales
     *   - Sin parámetro  → local + DecolectaService (RENIEC) si es DNI de 8 dígitos
     * GET /admin/buscar-dni?tipo_doc=DNI&dni=12345678[&local_only=1]
     */
    public function buscarDni(Request $request)
    {
        $tipoDoc   = strtoupper(trim($request->input('tipo_doc', 'DNI')));
        $doc       = trim($request->input('dni', ''));
        $localOnly = $request->has('local_only');

        if (empty($doc)) {
            return response()->json(['exists' => false, 'exists_external' => false]);
        }

        // Verificar si ya existe un usuario del sistema con ese documento
        $usuarioExistente = User::where('username', $doc)->first();
        $existingUser = $usuarioExistente ? [
            'nombre' => trim(($usuarioExistente->apellido_paterno ?? '') . ' ' . ($usuarioExistente->name ?? '')),
            'role'   => $usuarioExistente->role,
            'status' => $usuarioExistente->status,
            'id'     => $usuarioExistente->id,
        ] : null;

        // 1. Buscar en mon_profesionales (tabla local)
        $profesional = \App\Models\Profesional::where('doc', $doc)->first();

        if ($profesional) {
            return response()->json([
                'exists'           => true,
                'exists_external'  => false,
                'existing_user'    => $existingUser,
                'tipo_doc'         => $profesional->tipo_doc,
                'apellido_paterno' => $profesional->apellido_paterno,
                'apellido_materno' => $profesional->apellido_materno,
                'nombres'          => $profesional->nombres,
                'email'            => $profesional->email ?? '',
            ]);
        }

        // Retornar solo local si se indica (flujo en 2 pasos del frontend)
        if ($localOnly) {
            return response()->json([
                'exists'          => false,
                'exists_external' => false,
                'existing_user'   => $existingUser,
            ]);
        }

        // 2. API externa (DecolectaService) – solo para DNI de 8 dígitos
        if ($tipoDoc === 'DNI' && preg_match('/^\d{8}$/', $doc)) {
            $decolecta = new \App\Services\DecolectaService();
            $result    = $decolecta->consultarDni($doc);

            if (isset($result['error']) && $result['error'] === 'quota_exceeded') {
                return response()->json([
                    'exists'          => false,
                    'exists_external' => false,
                    'quota_exceeded'  => true,
                    'existing_user'   => $existingUser,
                    'message'         => 'Límite mensual de validaciones RENIEC excedido.',
                ]);
            }

            if (isset($result['success']) && $result['success']) {
                $data = $result['data'];
                return response()->json([
                    'exists'           => false,
                    'exists_external'  => true,
                    'existing_user'    => $existingUser,
                    'tipo_doc'         => 'DNI',
                    'apellido_paterno' => $data['apellido_paterno'],
                    'apellido_materno' => $data['apellido_materno'],
                    'nombres'          => $data['nombres'],
                    'email'            => '',
                    'remaining_tokens' => $data['remaining_tokens'] ?? null,
                ]);
            }
        }

        return response()->json([
            'exists'          => false,
            'exists_external' => false,
            'existing_user'   => $existingUser,
        ]);
    }

    /**
     * Cambio de estado vía AJAX (Toggle Status)
     */
    public function toggleStatus(User $user)
    {
        // Seguridad: Evitar que el admin se bloquee a sí mismo
        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes bloquear tu propia cuenta.'
            ], 403);
        }

        $user->status = ($user->status === 'active') ? 'inactive' : 'active';
        $user->save();

        $msg = ($user->status === 'active') ? 'Activado' : 'Bloqueado';

        return response()->json([
            'success' => true,
            'message' => 'Usuario ' . $msg . ' correctamente.'
        ]);
    }
}