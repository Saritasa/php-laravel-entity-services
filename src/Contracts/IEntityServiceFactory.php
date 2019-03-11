<?php

namespace Saritasa\LaravelEntityServices\Contracts;

use Illuminate\Contracts\Container\BindingResolutionException;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceRegisterException;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceException;

/**
 * Restful services factory interface.
 */
interface IEntityServiceFactory
{
    /**
     * Returns needed restful service for model class.
     *
     * @param string $modelClass Model class
     *
     * @return IEntityService
     *
     * @throws EntityServiceException
     * @throws BindingResolutionException
     */
    public function build(string $modelClass): IEntityService;

    /**
     * Register custom service to serve model class.
     *
     * @param string $modelClass Model class for which custom service is needed
     * @param string $entityServiceClass Custom service class
     *
     * @return void
     *
     * @throws EntityServiceRegisterException
     */
    public function register(string $modelClass, string $entityServiceClass): void;
}
