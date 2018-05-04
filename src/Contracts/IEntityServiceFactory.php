<?php

namespace Saritasa\LaravelEntityServices\Contracts;

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
     */
    public function build(string $modelClass): IEntityService;

    /**
     * Register custom service to serve model class.
     *
     * @param string $modelClass Model class for which custom service is needed
     * @param string $entityServiceClass Custom service class
     *
     * @return void
     */
    public function register(string $modelClass, string $entityServiceClass): void;
}
