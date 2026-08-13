<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsCronogramaViewer
{
    /**
     * Permite el acceso a usuarios con rol 'admin' o 'visor_cronograma'.
     * Bloquea a cualquier otro rol con 403.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'visor_cronograma'])) {
            abort(403, 'Acceso denegado: se requiere rol de administrador o visor de cronograma.');
        }

        return $next($request);
    }
}
