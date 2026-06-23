<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\EspacioRepositoryInterface;
use App\Interfaces\EspacioServiceInterface;
use App\Interfaces\EscuelaServiceInterface;
use App\Repositories\EspacioRepository;
use App\Services\EspacioService;
use App\Services\EscuelaService;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Registra los bindings de interfaces con sus implementaciones.
     * Equivalente al IoC Container de Spring Boot.
     */
    public function register(): void
    {
        $this->app->bind(EspacioRepositoryInterface::class, EspacioRepository::class);
        $this->app->bind(EspacioServiceInterface::class, EspacioService::class);
        $this->app->bind(EscuelaServiceInterface::class, EscuelaService::class);
    }

    public function boot(): void
    {
        //
    }
}
