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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
