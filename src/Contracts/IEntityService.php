<?php

namespace Saritasa\LaravelEntityServices\Contracts;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceOperationException;
use Saritasa\LaravelRepositories\Contracts\IRepository;

/**
 * Manager to work with entities.
 */
interface IEntityService
{
    /**
     * Creates new entity.
     *
     * @param array $modelParams Model needed parameters
     *
     * @return Model
     *
     * @throws ValidationException
     * @throws EntityServiceOperationException
     */
    public function create(array $modelParams): Model;

    /**
     * Updates entity.
     *
     * @param Model $model Model to update
     * @param array $modelParams Model needed parameters
     *
     * @throws ValidationException
     * @throws EntityServiceOperationException
     *
     * @return Model
     */
    public function update(Model $model, array $modelParams): Model;

    /**
     * Deletes entity.
     *
     * @param Model $model Model to delete
     *
     * @throws EntityServiceOperationException
     *
     * @return void
     */
    public function delete(Model $model): void;

    /**
     * Returns repository for entity.
     *
     * @return IRepository
     */
    public function getRepository(): IRepository;
}
