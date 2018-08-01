<?php

namespace Saritasa\LaravelEntityServices;

use Illuminate\Support\ServiceProvider;
use Saritasa\LaravelEntityServices\Contracts\IEntityServiceFactory;
use Saritasa\LaravelEntityServices\Services\EntityServiceFactory;

/**
 * Package providers. Registers package implementation in DI container.
 * Make settings needed to correct work.
 */
class LaravelEntityServicesServiceProvider extends ServiceProvider
{
    /**
     * Register package implementation in DI container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(IEntityServiceFactory::class, EntityServiceFactory::class);
    }
}
