<?php

namespace Saritasa\LaravelEntityServices\Services;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Saritasa\LaravelEntityServices\Contracts\IEntityService;
use Saritasa\LaravelEntityServices\Events\EntityCreatedEvent;
use Saritasa\LaravelEntityServices\Events\EntityDeletedEvent;
use Saritasa\LaravelEntityServices\Events\EntityUpdatedEvent;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceException;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceOperationException;
use Saritasa\LaravelRepositories\Contracts\IRepository;
use Saritasa\LaravelRepositories\Exceptions\RepositoryException;

/**
 * Restful service to create, update and delete model. Has ability to validate model attributes.
 * Provides access to handled model repository.
 */
class EntityService implements IEntityService
{
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
     * Events dispatcher.
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * Validation rules.
     *
     * @var array
     */
    protected $validationRules = [];

    /**
     * Default restful service for all entities.
     *
     * @param string $className Entity class name
     * @param IRepository $repository Current entity repository
     * @param Factory $validatorFactory Validation factory
     * @param Dispatcher $dispatcher Events dispatcher
     */
    public function __construct(
        string $className,
        IRepository $repository,
        Factory $validatorFactory,
        Dispatcher $dispatcher
    ) {
        $this->repository = $repository;
        $this->validatorFactory = $validatorFactory;
        $this->modelClass = $className;
        $this->dispatcher = $dispatcher;
    }

    /** {@inheritdoc} */
    public function create(array $modelParams): Model
    {
        $this->validate($modelParams);

        try {
            $model = $this->repository->create(new $this->modelClass($modelParams));
        } catch (RepositoryException $exception) {
            throw new EntityServiceOperationException($exception->getMessage(), $exception);
        }

        $this->dispatcher->dispatch(new EntityCreatedEvent($model));

        return $model;
    }

    /** {@inheritdoc} */
    public function update(Model $model, array $modelParams): Model
    {
        $this->validateServedEntity($model);
        $this->validate($modelParams, $this->getValidationRulesForAttributes($modelParams));

        try {
            $this->repository->save($model->fill($modelParams));
        } catch (RepositoryException $exception) {
            throw new EntityServiceOperationException($exception->getMessage(), $exception);
        }

        $this->dispatcher->dispatch(new EntityUpdatedEvent($model));

        return $model;
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
        $modelRules = empty($rules) ? $this->getValidationRules() : $rules;
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
        $this->validateServedEntity($model);
        $this->checkBeforeDelete($model);
        $id = $model->getKey();
        try {
            $this->repository->delete($model);
        } catch (RepositoryException $exception) {
            throw new EntityServiceOperationException($exception->getMessage(), $exception);
        }

        $this->dispatcher->dispatch(new EntityDeletedEvent($this->modelClass, $id));
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
        $validator = $this->validatorFactory->make($data, $rules ?? $this->getValidationRules());
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Returns validation rules.
     *
     * @return array
     */
    protected function getValidationRules(): array
    {
        $validationRulesFromRepository = $this->repository->getModelValidationRules();

        return !empty($validationRulesFromRepository) ? $validationRulesFromRepository : $this->validationRules;
    }

    /**
     * Validates that provided entity can be served by this service.
     *
     * @param Model $model Model to validate
     *
     * @return void
     *
     * @throws EntityServiceException
     */
    protected function validateServedEntity(Model $model): void
    {
        if (!$model instanceof $this->modelClass) {
            throw new EntityServiceException("This service can serve only {$this->modelClass} entities.");
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
