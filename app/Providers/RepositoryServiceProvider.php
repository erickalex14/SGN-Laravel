<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //Registrar el repositorio de empresas
        $this->app->bind(
            \App\Repositories\Contracts\Directory\EmpresaRepositoryInterface::class,
            \App\Repositories\Directory\EmpresaRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\Directory\ClienteRepositoryInterface::class,
            \App\Repositories\Directory\ClienteRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\Directory\SucursalRepositoryInterface::class,
            \App\Repositories\Directory\SucursalRepository::class
        );

        // Repetir para ClienteRepository y SucursalRepository segun se implementen
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
