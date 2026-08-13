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

