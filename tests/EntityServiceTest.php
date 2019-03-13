<?php

namespace Tests\Unit;

use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Saritasa\LaravelEntityServices\Events\EntityCreatedEvent;
use Saritasa\LaravelEntityServices\Events\EntityDeletedEvent;
use Saritasa\LaravelEntityServices\Events\EntityUpdatedEvent;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceException;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceOperationException;
use Saritasa\LaravelEntityServices\Services\EntityService;
use Saritasa\LaravelEntityServices\Tests\TestEntity;
use Saritasa\LaravelRepositories\Contracts\IRepository;
use Saritasa\LaravelRepositories\Exceptions\RepositoryException;

class EntityServiceTest extends TestCase
{
    /**
     * Repository for tested entity mock.
     *
     * @var MockInterface|IRepository
     */
    protected $repositoryMock;

    /**
     * Events dispatcher mock.
     *
     * @var Dispatcher|MockInterface
     */
    protected $dispatcher;

    /**
     * Sets up test configuration.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(IRepository::class);
        $this->repositoryMock->shouldReceive('getModelValidationRules')->andReturn([]);
        $this->dispatcher = Mockery::mock(Dispatcher::class);
    }

    /**
     * Tests create method.
     *
     * @dataProvider createMethodParams
     *
     * @param string $servedClass Served class by tested service
     * @param bool $isDataValid Shows whether new data should be pass validation
     * @param bool $isExceptionOnRepositorySide Shows whether exception should be thrown on repository side
     * @param string|null $exception Exception which should be thrown
     *
     * @return void
     *
     * @throws EntityServiceOperationException
     * @throws InvalidArgumentException
     * @throws ValidationException
     */
    public function testCreateMethod(
        string $servedClass,
        bool $isDataValid,
        bool $isExceptionOnRepositorySide,
        ?string $exception
    ): void {
        if ($isExceptionOnRepositorySide) {
            $this->repositoryMock
                ->shouldReceive('create')
                ->andThrow(new RepositoryException($this->repositoryMock));
        } else {
            $this->repositoryMock
                ->shouldReceive('create')
                ->andReturnUsing(function (Model $entity) {
                    return $entity;
                });
        }

        $this->dispatcher
            ->shouldReceive('dispatch')
            ->andReturnUsing(function (EntityCreatedEvent $event) use ($servedClass) {
                $this->assertEquals($servedClass, $event->getModelClass());
            });
        $restfulService = new EntityService(
            $servedClass,
            $this->repositoryMock,
            $this->getValidatorFactory($isDataValid),
            $this->dispatcher
        );

        if ($exception) {
            $this->expectException($exception);
        }

        $createdEntity = $restfulService->create([]);
        $this->assertEquals($servedClass, get_class($createdEntity));
    }

    /**
     * Returns different params for create entity methods.
     *
     * @return array
     */
    public function createMethodParams(): array
    {
        $servedEntity = get_class(new class extends Model{});

        return [
            [TestEntity::class, false, false, ValidationException::class],
            [TestEntity::class, false, true, ValidationException::class],
            [TestEntity::class, true, true, EntityServiceOperationException::class],
            [$servedEntity, true, false, null],
            [TestEntity::class, true, false, null],
        ];
    }

    /**
     * Tests update method.
     *
     * @dataProvider updateMethodParams
     *
     * @param string $servedClass Served class by tested service
     * @param bool $isDataValid Shows whether new data should be pass validation
     * @param bool $isExceptionOnRepositorySide Shows whether exception should be thrown on repository side
     * @param string|null $exception Exception which should be thrown
     *
     * @return void
     *
     * @throws EntityServiceException
     * @throws EntityServiceOperationException
     * @throws InvalidArgumentException
     * @throws ValidationException
     */
    public function testUpdateMethod(
        string $servedClass,
        bool $isDataValid,
        bool $isExceptionOnRepositorySide,
        ?string $exception
    ): void {
        $updatedEntity = new TestEntity([]);

        if ($isExceptionOnRepositorySide) {
            $this->repositoryMock
                ->shouldReceive('save')
                ->andThrow(new RepositoryException($this->repositoryMock));
        } else {
            $this->repositoryMock
                ->shouldReceive('save')
                ->withArgs([$updatedEntity])
                ->andReturnUsing(function (TestEntity $entity) {
                    return $entity;
                });
        }

        $this->dispatcher
            ->shouldReceive('dispatch')
            ->andReturnUsing(function (EntityUpdatedEvent $event) use ($updatedEntity, $servedClass) {
                $this->assertSame($updatedEntity, $event->getEntity());
                $this->assertEquals($servedClass, $event->getModelClass());
            });

        $restfulService = new EntityService(
            $servedClass,
            $this->repositoryMock,
            $this->getValidatorFactory($isDataValid),
            $this->dispatcher
        );

        if ($exception) {
            $this->expectException($exception);
        }

        $actualEntity = $restfulService->update($updatedEntity, []);

        $this->assertSame($updatedEntity, $actualEntity);
    }

    /**
     * Returns different params for update entity method.
     *
     * @return array
     */
    public function updateMethodParams(): array
    {
        $servedEntity = get_class(new class extends Model{});

        return [
            [$servedEntity, false, false, EntityServiceException::class],
            [$servedEntity, true, false, EntityServiceException::class],
            [$servedEntity, true, true, EntityServiceException::class],
            [TestEntity::class, false, false, ValidationException::class],
            [TestEntity::class, false, true, ValidationException::class],
            [TestEntity::class, true, true, EntityServiceOperationException::class],
            [TestEntity::class, true, false, null],
        ];
    }

    /**
     * Tests delete entity method.
     *
     * @dataProvider deleteMethodParams
     *
     * @param string $servedClass Served class by tested service
     * @param bool $isExceptionOnRepositorySide Shows whether exception should be thrown on repository side
     * @param string|null $exception Exception which should be thrown
     *
     * @throws EntityServiceOperationException
     * @throws InvalidArgumentException
     * @throws EntityServiceException
     * @throws Exception
     */
    public function testDeleteMethod(string $servedClass, bool $isExceptionOnRepositorySide, ?string $exception): void
    {
        $id = random_int(0, 20);
        $expectedEntity = new TestEntity([]);
        $expectedEntity->setAttribute('id', $id);

        if ($isExceptionOnRepositorySide) {
            $this->repositoryMock
                ->shouldReceive('delete')
                ->andThrow(new RepositoryException($this->repositoryMock));
        } else {
            $this->repositoryMock->shouldReceive('delete')->withArgs([$expectedEntity]);
        }

        $this->dispatcher
            ->shouldReceive('dispatch')
            ->andReturnUsing(function (EntityDeletedEvent $event) use ($servedClass, $id) {
                $this->assertEquals($id, $event->getId());
                $this->assertEquals($servedClass, $event->getModelClass());
            });

        $restfulService = new EntityService(
            $servedClass,
            $this->repositoryMock,
            $this->getValidatorFactory(false),
            $this->dispatcher
        );

        if ($exception) {
            $this->expectException($exception);
        }

        $restfulService->delete($expectedEntity);
    }

    /**
     * Returns different params for delete entity method.
     *
     * @return array
     */
    public function deleteMethodParams(): array
    {
        $servedEntity = get_class(new class extends Model{});

        return [
            [$servedEntity, false, EntityServiceException::class],
            [$servedEntity, true, EntityServiceException::class],
            [TestEntity::class, true, EntityServiceOperationException::class],
            [TestEntity::class, false, null],
        ];
    }

    /**
     * Tests get repository method.
     *
     * @dataProvider getRepositoryMethodParams
     *
     * @param string $servedClass Served class by tested service
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function testGetRepositoryMethod(string $servedClass): void
    {
        $this->repositoryMock->shouldReceive('getModelClass')->andReturn($servedClass);

        $restfulService = new EntityService(
            $servedClass,
            $this->repositoryMock,
            $this->getValidatorFactory(true),
            $this->dispatcher
        );

        $this->assertSame($this->repositoryMock, $restfulService->getRepository());
        $this->assertEquals($servedClass, $restfulService->getRepository()->getModelClass());
    }

    /**
     * Returns different params for delete entity method.
     *
     * @return array
     */
    public function getRepositoryMethodParams(): array
    {
        $servedEntity = get_class(new class extends Model{});

        return [[$servedEntity], [TestEntity::class]];
    }

    /**
     * Returns validators factory mock.
     *
     * @param bool $success Shows whether should be success with validation
     * @param array $data Validation data
     * @param array $rules Validation rules
     *
     * @return Factory|MockInterface
     *
     * @throws InvalidArgumentException
     */
    protected function getValidatorFactory(bool $success, array $data = [], array $rules = []): Factory
    {
        $validatorMock = Mockery::mock(Validator::class);
        $validatorMock->shouldReceive('fails')->andReturn(!$success);
        $validationFactory = Mockery::mock(Factory::class);
        $validationFactory->shouldReceive('make')->withArgs([$data, $rules])->andReturn($validatorMock);

        return $validationFactory;
    }
}
