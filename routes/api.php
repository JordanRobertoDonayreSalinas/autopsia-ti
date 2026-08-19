<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RenipressController;
use App\Http\Controllers\SpeedtestController;

Route::post('/renipress/consultar', [RenipressController::class, 'consultar']);

// --- RUTAS API SPEEDTEST DE VELOCIDAD DE INTERNET ---
Route::prefix('speedtest')->group(function () {
    Route::get('/ping', [SpeedtestController::class, 'ping']);
    Route::get('/download', [SpeedtestController::class, 'download']);
    Route::post('/upload', [SpeedtestController::class, 'upload']);
});

// --- RUTAS API REST V1 PARA APP MULTIPLATAFORMA (FLUTTER / DESKTOP / MOBILE / TABLET) ---
Route::prefix('v1')->group(function () {
    // Login público: emite un token de Sanctum (ver LoginController::apiLogin).
    Route::post('/login', [\App\Http\Controllers\LoginController::class, 'apiLogin']);
    Route::get('/version', [\App\Http\Controllers\OfflineSyncController::class, 'apiVersion']);
    Route::get('/schema', [\App\Http\Controllers\Api\SchemaApiController::class, 'show']);
    Route::get('/catalog', [\App\Http\Controllers\OfflineSyncController::class, 'descargarDatosCampo']);

    // Escritura de datos de campo: requiere el token emitido en /login.
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/sync', [\App\Http\Controllers\OfflineSyncController::class, 'sincronizarLoteOffline']);
        Route::post('/reuniones/sync', [\App\Http\Controllers\OfflineSyncController::class, 'sincronizarReunionesOffline']);

        // Gestión de usuarios (solo rol admin, verificado dentro del controlador).
        Route::get('/users/buscar-dni', [\App\Http\Controllers\Api\UsersApiController::class, 'buscarDni']);
        Route::post('/users', [\App\Http\Controllers\Api\UsersApiController::class, 'store']);
        Route::put('/users/{user}', [\App\Http\Controllers\Api\UsersApiController::class, 'update']);
        Route::patch('/users/{user}/toggle-status', [\App\Http\Controllers\Api\UsersApiController::class, 'toggleStatus']);

        // Edición del catálogo de establecimientos (solo admin/operador).
        Route::put('/establecimientos/{id}', [\App\Http\Controllers\Api\EstablecimientosApiController::class, 'update']);
        Route::get('/establecimientos/{id}/consultar-renipress', [\App\Http\Controllers\Api\EstablecimientosApiController::class, 'consultarRenipress']);

        // Mi Perfil: cualquier usuario autenticado edita su propio registro.
        Route::put('/perfil', [\App\Http\Controllers\Api\PerfilApiController::class, 'update']);

        // Listado real de Actas de Diagnóstico Situacional (espejo de MonitoreoController::index).
        Route::get('/monitoreo', [\App\Http\Controllers\Api\MonitoreoApiController::class, 'index']);
        Route::get('/monitoreo/{id}', [\App\Http\Controllers\Api\MonitoreoApiController::class, 'show']);
        Route::post('/monitoreo/{id}/modulos', [\App\Http\Controllers\Api\MonitoreoApiController::class, 'guardarModulo']);
        Route::post('/monitoreo/{id}/anular', [\App\Http\Controllers\Api\MonitoreoApiController::class, 'anular']);
    });

    // --- DASHBOARD Y DATOS PARA APP FLUTTER ---
    Route::get('/dashboard/stats',      [\App\Http\Controllers\Api\DashboardApiController::class, 'stats']);
    Route::get('/establecimientos/map', [\App\Http\Controllers\Api\DashboardApiController::class, 'mapMarkers']);
    Route::get('/users',                [\App\Http\Controllers\Api\DashboardApiController::class, 'users']);
});