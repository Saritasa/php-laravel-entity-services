<?php

namespace Saritasa\LaravelEntityServices;

use Illuminate\Contracts\Container\BindingResolutionException;
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

    /**
     * Make package settings needed to correct work.
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__ . '/../config/laravel_entity_services.php' =>
                    $this->app->make('path.config') . DIRECTORY_SEPARATOR . 'laravel_entity_services.php',
            ],
            'laravel_repositories'
        );
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel_entity_services.php', 'laravel_entity_services');

        $this->registerCustomBindings();
    }

    /**
     * Register custom entity services implementations.
     *
     * @return void
     *
     * @throws BindingResolutionException
     */
    protected function registerCustomBindings(): void
    {
        $entityServiceFactory = $this->app->make(IEntityServiceFactory::class);

        foreach (config('laravel_entity_services.bindings') as $className => $entityService) {
            $entityServiceFactory->register($className, $entityService);
        }
    }
}
