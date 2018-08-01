<?php

namespace Saritasa\LaravelEntityServices\Services;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Database\ConnectionInterface;
use Saritasa\LaravelEntityServices\Contracts\IEntityService;
use Saritasa\LaravelEntityServices\Contracts\IEntityServiceFactory;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceException;
use Saritasa\LaravelRepositories\Contracts\IRepositoryFactory;
use Throwable;

/**
 * Entity services factory.
 */
class EntityServiceFactory implements IEntityServiceFactory
{
    /**
     * Collection of correspondences model to service.
     *
     * @var array
     */
    protected $registeredServices = [];

    /**
     * Repository factory.
     *
     * @var IRepositoryFactory
     */
    protected $repositoryFactory;

    /**
     * Validation factory.
     *
     * @var ValidationFactory
     */
    protected $factory;

    /**
     * Default db connection.
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
     * Already created instances.
     *
     * @var array
     */
    protected $sharedInstances = [];

    /**
     * Entity services factory.
     *
     * @param IRepositoryFactory $repositoryFactory Repository factory
     * @param ValidationFactory $factory Validation factory
     * @param ConnectionInterface $connection Database connection
     * @param Dispatcher $dispatcher Events dispatcher
     */
    public function __construct(
        IRepositoryFactory $repositoryFactory,
        ValidationFactory $factory,
        ConnectionInterface $connection,
        Dispatcher $dispatcher
    ) {
        $this->repositoryFactory = $repositoryFactory;
        $this->factory = $factory;
        $this->connection = $connection;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function build(string $modelClass): IEntityService
    {
        if (!isset($this->sharedInstances[$modelClass]) || $this->sharedInstances[$modelClass] === null) {
            $this->sharedInstances[$modelClass] = $this->buildEntityService($modelClass);
        }
        return $this->sharedInstances[$modelClass];
    }

    /**
     * Build repository by model class from registered instances or creates default.
     *
     * @param string $modelClass Model class
     *
     * @return IEntityService
     *
     * @throws EntityServiceException
     */
    protected function buildEntityService(string $modelClass): IEntityService
    {
        try {
            if (isset($this->registeredServices[$modelClass])) {
                return new $this->registeredServices[$modelClass](
                    $modelClass,
                    $this,
                    $this->repositoryFactory->getRepository($modelClass),
                    $this->factory,
                    $this->connection,
                    $this->dispatcher
                );
            }

            return new EntityService(
                $modelClass,
                $this,
                $this->repositoryFactory->getRepository($modelClass),
                $this->factory,
                $this->connection,
                $this->dispatcher
            );
        } catch (Throwable $exception) {
            throw new EntityServiceException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /** {@inheritdoc} */
    public function register(string $modelClass, string $entityServiceClass): void
    {
        $this->registeredServices[$modelClass] = $entityServiceClass;
    }
}
