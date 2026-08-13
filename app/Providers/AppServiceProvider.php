<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Configurar Carbon para que las fechas salgan en español (ej: "hace 2 minutos")
        Carbon::setLocale(config('app.locale'));
        setlocale(LC_TIME, 'es_ES.utf8', 'es_ES', 'es');

        // 2. Opcional: Evitar problemas con longitudes de índices en bases de datos antiguas
        Schema::defaultStringLength(191);
    }
}