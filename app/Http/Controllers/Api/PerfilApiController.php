<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Espejo API (Sanctum) de UsuarioController::perfil/perfilUpdate — a
 * diferencia de UsersApiController, aquí cualquier usuario autenticado edita
 * SU PROPIO registro (sin importar el rol), igual que la ruta web
 * PUT /usuario/mi-perfil.
 */
class PerfilApiController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->fill($request->only('name', 'apellido_paterno', 'apellido_materno', 'email'));

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Tu perfil ha sido actualizado correctamente.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'apellido_paterno' => $user->apellido_paterno,
                'apellido_materno' => $user->apellido_materno,
                'nombre_completo' => $user->full_name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'updated_at' => optional($user->updated_at)->toIso8601String(),
            ],
        ]);
    }
}
