<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'is_admin'              => \App\Http\Middleware\IsAdmin::class,
            'is_operador_or_admin'  => \App\Http\Middleware\IsOperadorOrAdmin::class,
            'is_cronograma_viewer'  => \App\Http\Middleware\IsCronogramaViewer::class,
        ]);
        
        $middleware->web(append: [
            \App\Http\Middleware\CheckUserStatus::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            '/logout',
            '/usuario/ajax/guardar-deteccion-hardware',
            '/usuario/ajax/hardware-directo',
            // Va con las dos anteriores: es el paso alternativo del mismo flujo de
            // detección y solo devuelve un token aleatorio en caché, sin tocar datos.
            // Sin esta línea responde 419 (HTML) y el fetch del navegador revienta al
            // hacer .json() sobre la página de error.
            '/usuario/ajax/hardware-token',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
