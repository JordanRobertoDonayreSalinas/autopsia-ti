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
    Route::middleware('auth:sanctum')->post('/sync', [\App\Http\Controllers\OfflineSyncController::class, 'sincronizarLoteOffline']);

    // --- DASHBOARD Y DATOS PARA APP FLUTTER ---
    Route::get('/dashboard/stats',      [\App\Http\Controllers\Api\DashboardApiController::class, 'stats']);
    Route::get('/establecimientos/map', [\App\Http\Controllers\Api\DashboardApiController::class, 'mapMarkers']);
    Route::get('/users',                [\App\Http\Controllers\Api\DashboardApiController::class, 'users']);
});