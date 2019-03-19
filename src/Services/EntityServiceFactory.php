<?php

namespace Saritasa\LaravelEntityServices\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Saritasa\LaravelEntityServices\Contracts\IEntityService;
use Saritasa\LaravelEntityServices\Contracts\IEntityServiceFactory;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceRegisterException;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceException;
use Saritasa\LaravelRepositories\Contracts\IRepositoryFactory;
use Saritasa\LaravelRepositories\Exceptions\RepositoryException;

/**
 * Entity services factory.
 */
class EntityServiceFactory implements IEntityServiceFactory
{
    /**
     * Repository factory.
     *
     * @var IRepositoryFactory
     */
    protected $repositoryFactory;

    /**
     * Collection of correspondences model to service.
     *
     * @var array
     */
    protected $registeredServices = [];

    /**
     * Already created instances.
     *
     * @var array
     */
    protected $sharedInstances = [];

    /**
     * DI container instance.
     *
     * @var Container
     */
    protected $container;

    /**
     * Entity services factory.
     *
     * @param Container $container DI container instance
     * @param IRepositoryFactory $repositoryFactory Repository factory
     */
    public function __construct(Container $container, IRepositoryFactory $repositoryFactory)
    {
        $this->container = $container;
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @throws BindingResolutionException
     */
    public function build(string $modelClass): IEntityService
    {
        if (!isset($this->sharedInstances[$modelClass]) || $this->sharedInstances[$modelClass] === null) {
            $this->sharedInstances[$modelClass] = $this->buildEntityService($modelClass);
        }
        return $this->sharedInstances[$modelClass];
    }

    /**
     * Build entity service by model class from registered instances or creates default.
     *
     * @param string $modelClass Model class to build entity service
     *
     * @return IEntityService
     *
     * @throws EntityServiceException
     * @throws BindingResolutionException
     */
    protected function buildEntityService(string $modelClass): IEntityService
    {
        try {
            $entityServiceClass = $this->registeredServices[$modelClass] ?? EntityService::class;

            $parameters = [];

            if ($entityServiceClass === EntityService::class ||
                is_subclass_of($entityServiceClass, EntityService::class)
            ) {
                $parameters = [
                    'className' => $modelClass,
                    'repository' => $this->repositoryFactory->getRepository($modelClass),
                ];
            }

            return $this->container->make($entityServiceClass, $parameters);
        } catch (RepositoryException $exception) {
            throw new EntityServiceException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /** {@inheritdoc} */
    public function register(string $modelClass, string $entityServiceClass): void
    {
        if (!is_subclass_of($modelClass, Model::class)) {
            throw new EntityServiceRegisterException("$modelClass must extend " . Model::class);
        }
        if (!is_subclass_of($entityServiceClass, IEntityService::class)) {
            throw new EntityServiceRegisterException("$entityServiceClass must implement " . IEntityService::class);
        }
        $this->registeredServices[$modelClass] = $entityServiceClass;
    }
}
