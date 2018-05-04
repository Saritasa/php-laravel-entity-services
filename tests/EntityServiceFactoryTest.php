<?php

namespace Saritasa\LaravelEntityServices\Tests;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Database\ConnectionInterface;
use PHPUnit\Framework\TestCase;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceException;
use Saritasa\LaravelEntityServices\Services\EntityServiceFactory;
use Saritasa\LaravelRepositories\Contracts\IRepositoryFactory;

class EntityServiceFactoryTest extends TestCase
{
    /** @var EntityServiceFactory */
    protected $entityServiceFactory;

    public function setUp()
    {
        parent::setUp();
        /** @var ConnectionInterface $connection */
        $connection = \Mockery::mock(ConnectionInterface::class);
        /** @var Factory $validationFactory */
        $validationFactory = \Mockery::mock(Factory::class);
        /** @var IRepositoryFactory $repositoryFactory */
        $repositoryFactory = \Mockery::mock(IRepositoryFactory::class);
        /** @var Dispatcher $dispatcher */
        $dispatcher = \Mockery::mock(Dispatcher::class);
        $this->entityServiceFactory = new EntityServiceFactory($repositoryFactory, $validationFactory, $connection, $dispatcher);
    }

    /**
     * Test build not existing model class.
     *
     * @throws EntityServiceException
     */
    public function testCantBuildNotExistingModel(): void
    {
        $this->expectException(EntityServiceException::class);
        $this->entityServiceFactory->build('model');
    }
}
