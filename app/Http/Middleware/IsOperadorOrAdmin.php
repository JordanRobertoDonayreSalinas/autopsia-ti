<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsOperadorOrAdmin
{
    /**
     * Permite el acceso a usuarios con rol 'admin' u 'operador'.
     * Bloquea a cualquier otro rol con 403.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'operador'])) {
            abort(403, 'Acceso denegado: se requiere rol de administrador u operador.');
        }

        return $next($request);
    }
}
