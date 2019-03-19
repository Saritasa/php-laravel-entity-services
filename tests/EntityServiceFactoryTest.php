<?php

namespace Saritasa\LaravelEntityServices\Tests;

use Error;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Container\ContextualBindingBuilder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceException;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceRegisterException;
use Saritasa\LaravelEntityServices\Services\EntityService;
use Saritasa\LaravelEntityServices\Services\EntityServiceFactory;
use Saritasa\LaravelRepositories\Contracts\IRepository;
use Saritasa\LaravelRepositories\Contracts\IRepositoryFactory;
use Saritasa\LaravelRepositories\Exceptions\RepositoryException;

class EntityServiceFactoryTest extends TestCase
{
    /**
     * Connection instance mock.
     *
     * @var Container|MockInterface
     */
    protected $container;

    /**
     * Repositories factory mock.
     *
     * @var IRepositoryFactory|MockInterface
     */
    protected $repositoryFactory;

    /**
     * Setup tests setting.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->container = Mockery::mock(Container::class);
        $this->repositoryFactory = Mockery::mock(IRepositoryFactory::class);
    }

    /**
     * Test register custom entity service for model with different cases.
     *
     * @dataProvider registerEntityServiceData
     *
     * @param string $model Model for which need to register custom entity service
     * @param string $serviceClass Custom entity service class
     * @param bool $expectException Shows whether should be thrown an exception
     *
     * @return void
     *
     * @throws EntityServiceRegisterException
     * @throws EntityServiceException
     * @throws InvalidArgumentException
     * @throws BindingResolutionException
     */
    public function testRegisterCustomRepositories(string $model, string $serviceClass, bool $expectException): void
    {
        $entityServiceFactory = new EntityServiceFactory($this->container, $this->repositoryFactory);

        if ($expectException) {
            $this->expectException(EntityServiceRegisterException::class);
        }

        $entityServiceFactory->register($model, $serviceClass);
        $expectedService = Mockery::mock($serviceClass);

        if (!$expectException) {
            $this->repositoryFactory->shouldReceive('getRepository')
                ->withArgs([$model])
                ->andReturn(Mockery::mock(IRepository::class));
            $this->container->shouldReceive('make')->andReturn($expectedService);
            $actualService = $entityServiceFactory->build($model);
            $this->assertSame($expectedService, $actualService);
        }
    }

    /**
     * Returns data to test different cases in register method.
     *
     * @return array
     */
    public function registerEntityServiceData(): array
    {
        $modelObject = new class extends Model{};

        return [
            ['Not object class', EntityService::class, true],
            [EntityService::class, EntityService::class, true],
            [get_class($modelObject), 'Not existing entity service', true],
            [get_class($modelObject), get_class($modelObject), true],
            [get_class($modelObject), EntityService::class, false],
        ];
    }

    /**
     * Tests that factory returns the same instance each time when it called.
     *
     * @dataProvider buildInstancesData
     *
     * @param string $modelClass Model for which need to register custom entity service
     * @param string|null $entityServiceClass Custom entity service class
     *
     * @return void
     *
     * @throws EntityServiceException
     * @throws EntityServiceRegisterException
     * @throws InvalidArgumentException
     * @throws BindingResolutionException
     */
    public function testEachTimeReturnsTheSameInstance(string $modelClass, ?string $entityServiceClass): void
    {
        $entityServiceFactory = new EntityServiceFactory($this->container, $this->repositoryFactory);

        $this->repositoryFactory->shouldReceive('getRepository')
            ->withArgs([$modelClass])
            ->andReturn(Mockery::mock(IRepository::class));

        if ($entityServiceClass) {
            $entityServiceFactory->register($modelClass, $entityServiceClass);
        }

        $entityServiceClass = $entityServiceClass ?? EntityService::class;

        $this->container->shouldReceive('make')->andReturn(Mockery::mock($entityServiceClass));

        $firstInstance = $entityServiceFactory->build($modelClass);
        $secondInstance = $entityServiceFactory->build($modelClass);

        $this->assertSame($firstInstance, $secondInstance);
    }

    /**
     * Returns data for test each time returns the same instance.
     *
     * @return array
     */
    public function buildInstancesData(): array
    {
        $modelObject = new class extends Model{};

        return [
            [get_class($modelObject), EntityService::class],
            [get_class($modelObject), null],
        ];
    }

    /**
     * Tests that when exception/error appears in building process it converts in entity service exception.
     *
     * @dataProvider entityExceptionsData
     *
     * @param string $exception Exception class to test
     * @param bool $shouldBeCached Shows whether this exception should be cached
     *
     * @return void
     *
     * @throws BindingResolutionException
     * @throws EntityServiceException
     * @throws InvalidArgumentException
     */
    public function testEntityServiceExceptionWillThrownWhenAnyErrorWhileBuilding(string $exception, bool $shouldBeCached): void
    {
        $entityServiceFactory = new EntityServiceFactory($this->container, $this->repositoryFactory);

        $this->repositoryFactory->shouldReceive('getRepository')
            ->andReturn(Mockery::mock(IRepository::class));
        $contextualBindingBuilder = Mockery::mock(ContextualBindingBuilder::class);
        $contextualBindingBuilder->shouldReceive('needs', 'give')->andReturnSelf();
        $this->container->shouldReceive('when')
            ->withArgs([EntityService::class])
            ->andReturn($contextualBindingBuilder);
        $this->container->shouldReceive('make')->andThrow(Mockery::mock($exception));
        $this->expectException($shouldBeCached ? EntityServiceException::class : $exception);

        $modelObject = new class extends Model{};
        $entityServiceFactory->build(get_class($modelObject));
    }

    /**
     * Returns data for test each time returns the same instance.
     *
     * @return array
     */
    public function entityExceptionsData(): array
    {
        return [
            [RepositoryException::class, true],
            [BindingResolutionException::class, false],
            [Error::class, false],
            [RuntimeException::class, false],
        ];
    }
}
