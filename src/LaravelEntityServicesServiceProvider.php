<?php

namespace Saritasa\LaravelEntityServices;

use Illuminate\Support\ServiceProvider;
use Saritasa\LaravelEntityServices\Contracts\IEntityServiceFactory;
use Saritasa\LaravelEntityServices\Services\EntityServiceFactory;

class LaravelEntityServicesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(IEntityServiceFactory::class, EntityServiceFactory::class);
    }
}
