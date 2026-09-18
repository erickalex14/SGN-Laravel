<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;
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
        date_default_timezone_set(config('app.timezone', 'America/Guayaquil'));
        Carbon::setLocale(config('app.locale', 'es'));

        $appUrl = config('app.url', 'https://novitec.com.ec/sgn');

        if (config('app.env') === 'production' || env('FORCE_HTTPS', true) || request()->server('HTTP_X_FORWARDED_PROTO') === 'https' || str_contains($appUrl, 'https://') || !app()->isLocal()) {
            URL::forceScheme('https');
            URL::forceRootUrl($appUrl);
        }

        // Configurar el Paginador para que siempre genere enlaces absolutos con el prefijo /sgn correcto
        Paginator::currentPathResolver(function () use ($appUrl) {
            $path = request()->path();
            return rtrim($appUrl, '/') . '/' . ltrim($path, '/');
        });

        Paginator::useBootstrapFive();
    }
}
