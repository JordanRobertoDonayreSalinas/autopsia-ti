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
    Route::get('/version', [\App\Http\Controllers\OfflineSyncController::class, 'apiVersion']);
    Route::get('/catalog', [\App\Http\Controllers\OfflineSyncController::class, 'descargarDatosCampo']);
    Route::post('/sync', [\App\Http\Controllers\OfflineSyncController::class, 'sincronizarLoteOffline']);
});

