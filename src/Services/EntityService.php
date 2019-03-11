<?php

namespace Saritasa\LaravelEntityServices\Services;

use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Saritasa\LaravelEntityServices\Contracts\IEntityService;
use Saritasa\LaravelEntityServices\Contracts\IEntityServiceFactory;
use Saritasa\LaravelEntityServices\Events\EntityCreatedEvent;
use Saritasa\LaravelEntityServices\Events\EntityDeletedEvent;
use Saritasa\LaravelEntityServices\Events\EntityUpdatedEvent;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceOperationException;
use Saritasa\LaravelRepositories\Contracts\IRepository;
use Throwable;

/**
 * Restful service to create, update and delete model. Has ability to validate model attributes.
 * Provides access to handled model repository.
 */
class EntityService implements IEntityService
{
    /**
     * Restful services factory realization.
     *
     * @var IEntityServiceFactory
     */
    protected $restfulServiceFactory;

    /**
     * Current entity repository.
     *
     * @var IRepository
     */
    protected $repository;

    /**
     * Entity class name.
     *
     * @var string
     */
    protected $modelClass;

    /**
     * Validation factory.
     *
     * @var Factory
     */
    protected $validatorFactory;

    /**
     * Connection interface realization.
     *
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * Events dispatcher.
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * Default restful service for all entities.
     *
     * @param string $className Entity class name
     * @param IEntityServiceFactory $restfulServiceFactory Restful services factory realization
     * @param IRepository $repository Current entity repository
     * @param Factory $validatorFactory Validation factory
     * @param ConnectionInterface $connection Connection interface realization
     * @param Dispatcher $dispatcher Events dispatcher
     */
    public function __construct(
        string $className,
        IEntityServiceFactory $restfulServiceFactory,
        IRepository $repository,
        Factory $validatorFactory,
        ConnectionInterface $connection,
        Dispatcher $dispatcher
    ) {
        $this->repository = $repository;
        $this->validatorFactory = $validatorFactory;
        $this->modelClass = $className;
        $this->connection = $connection;
        $this->restfulServiceFactory = $restfulServiceFactory;
        $this->dispatcher = $dispatcher;
    }

    /** {@inheritdoc} */
    public function create(array $modelParams): Model
    {
        $this->validate($modelParams);
        return $this->handleTransaction(function () use ($modelParams) {
            $model = $this->repository->create(new $this->modelClass($modelParams));
            $this->dispatcher->dispatch(new EntityCreatedEvent($model));
            return $model;
        });
    }

    /** {@inheritdoc} */
    public function update(Model $model, array $modelParams): Model
    {
        $this->validate($modelParams, $this->getValidationRulesForAttributes($modelParams));
        return $this->handleTransaction(function () use ($model, $modelParams) {
            $this->repository->save($model->fill($modelParams));
            $this->dispatcher->dispatch(new EntityUpdatedEvent($model));
            return $model;
        });
    }

    /**
     * Return rules for given attributes.
     *
     * @param array $modelParams Updating fields
     * @param array $rules Custom validation rules
     *
     * @return array
     */
    protected function getValidationRulesForAttributes(array $modelParams, array $rules = []): array
    {
        $modelRules = empty($rules) ? $this->repository->getModelValidationRules() : $rules;
        return array_intersect_key($modelRules, $modelParams);
    }

    /**
     * Validates model before it can be deleted.
     *
     * @param Model $model Model to delete
     */
    protected function checkBeforeDelete(Model $model): void
    {
        // do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Model $model): void
    {
        $this->checkBeforeDelete($model);
        $this->handleTransaction(function () use ($model) {
            $id = $model->getKey();
            $this->repository->delete($model);
            $this->dispatcher->dispatch(new EntityDeletedEvent($this->modelClass, $id));
        });
    }

    /**
     * Wrap closure in db transaction.
     *
     * @param Closure $callback Callback which will be wrapped into transaction
     *
     * @return mixed
     *
     * @throws EntityServiceOperationException
     */
    protected function handleTransaction(Closure $callback)
    {
        try {
            $this->connection->beginTransaction();
            return tap($callback(), function () {
                $this->connection->commit();
            });
        } catch (Throwable $exception) {
            $this->connection->rollBack();
            throw new EntityServiceOperationException($exception->getMessage(), $exception);
        }
    }

    /**
     * Validates data.
     *
     * @param array $data Data to validate
     * @param array|null $rules Validation rules
     *
     * @throws ValidationException
     *
     * @return void
     */
    protected function validate(array $data, array $rules = null): void
    {
        $validator = $this->validatorFactory->make($data, $rules ?? $this->repository->getModelValidationRules());
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Returns current entity repository.
     *
     * @return IRepository
     */
    public function getRepository(): IRepository
    {
        return $this->repository;
    }
}
