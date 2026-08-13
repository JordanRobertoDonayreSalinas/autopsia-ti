<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Verifica que el usuario autenticado tenga el rol de administrador.
     * Si no, redirige al dashboard del usuario con 403.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Acceso denegado: se requiere rol de administrador.');
        }

        return $next($request);
    }
}
