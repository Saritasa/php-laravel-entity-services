<?php

namespace Tests\Unit;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Validation\ValidationException;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Saritasa\LaravelEntityServices\Contracts\IEntityServiceFactory;
use Saritasa\LaravelEntityServices\Events\EntityCreatedEvent;
use Saritasa\LaravelEntityServices\Events\EntityUpdatedEvent;
use Saritasa\LaravelEntityServices\Exceptions\EntityServiceOperationException;
use Saritasa\LaravelEntityServices\Services\EntityService;
use Saritasa\LaravelEntityServices\Tests\TestEntity;
use Saritasa\LaravelRepositories\Contracts\IRepository;
use Saritasa\LaravelRepositories\Exceptions\RepositoryException;

class EntityServiceTest extends TestCase
{
    /** @var MockInterface|IEntityServiceFactory */
    protected $restfulServiceFactoryMock;
    /** @var MockInterface|IRepository */
    protected $repositoryMock;
    /** @var MockInterface|ConnectionInterface */
    protected $connectionMock;
    /** @var TestEntity */
    protected $testEntity;
    /** @var Dispatcher|MockInterface */
    protected $dispatcher;

    public function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = \Mockery::mock(IRepository::class);
        $this->repositoryMock->shouldReceive('getModelValidationRules')->andReturn([]);
        $this->restfulServiceFactoryMock = \Mockery::mock(IEntityServiceFactory::class);
        $this->connectionMock = \Mockery::mock(ConnectionInterface::class);
        $this->connectionMock->shouldReceive('beginTransaction')->withArgs([])->andReturnNull();
        $this->connectionMock->shouldReceive('commit')->withArgs([])->andReturnNull();
        $this->connectionMock->shouldReceive('rollBack')->withArgs([])->andReturnNull();
        $this->dispatcher = \Mockery::mock(Dispatcher::class);
        $this->testEntity = new TestEntity([
            TestEntity::FIELD_1 => str_random(),
            TestEntity::FIELD_2 => str_random(),
            TestEntity::FIELD_3 => str_random(),
        ]);
    }

    /**
     * Test create method.
     *
     * @throws ValidationException
     * @throws EntityServiceOperationException
     */
    public function testCreateMethod(): void
    {
        $params = [
            TestEntity::FIELD_1 => str_random(),
            TestEntity::FIELD_2 => str_random(),
            TestEntity::FIELD_3 => str_random(),
        ];

        $this->repositoryMock->shouldReceive('create')->andReturnUsing(function (TestEntity $entity) {
            return $entity;
        });
        $expectedEntity = new TestEntity($params);
        $this->dispatcher
            ->shouldReceive('dispatch')
            ->andReturnUsing(function (EntityCreatedEvent $event) use ($expectedEntity) {
                $this->assertEquals($expectedEntity, $event->getEntity());
            });
        $restfulService = new EntityService(
            TestEntity::class,
            $this->restfulServiceFactoryMock,
            $this->repositoryMock,
            $this->getValidatorFactory(true, $params),
            $this->connectionMock,
            $this->dispatcher
        );

        $createdEntity = $restfulService->create($params);
        $this->assertEquals($expectedEntity, $createdEntity);
    }

    /**
     * Test update method.
     *
     * @throws EntityServiceOperationException
     * @throws ValidationException
     */
    public function testUpdateMethod(): void
    {
        $newParams = [
            TestEntity::FIELD_1 => str_random(),
            TestEntity::FIELD_2 => str_random(),
            TestEntity::FIELD_3 => str_random(),
        ];

        $this->repositoryMock
            ->shouldReceive('save')
            ->andReturnUsing(function (TestEntity $entity) use ($newParams) {
                $this->assertEquals($newParams[TestEntity::FIELD_1], $entity->field1);
                $this->assertEquals($newParams[TestEntity::FIELD_2], $entity->field2);
                $this->assertEquals($newParams[TestEntity::FIELD_3], $entity->field3);
                return $entity;
            });

        $expectedEntity = new TestEntity($newParams);

        $this->dispatcher
            ->shouldReceive('dispatch')
            ->andReturnUsing(function (EntityUpdatedEvent $event) use ($expectedEntity) {
                $this->assertEquals($expectedEntity, $event->getEntity());
            });

        $restfulService = new EntityService(
            TestEntity::class,
            $this->restfulServiceFactoryMock,
            $this->repositoryMock,
            $this->getValidatorFactory(true, $newParams),
            $this->connectionMock,
            $this->dispatcher
        );

        $restfulService->update($this->testEntity, $newParams);
    }

    public function testGetRepositoryMethod(): void
    {
        $restfulService = new EntityService(
            TestEntity::class,
            $this->restfulServiceFactoryMock,
            $this->repositoryMock,
            $this->getValidatorFactory(true),
            $this->connectionMock,
            $this->dispatcher
        );

        $this->assertEquals($this->repositoryMock, $restfulService->getRepository());
    }

    /**
     * @throws ValidationException
     */
    public function testValidateMethodIfException()
    {
        $someRules = [
            str_random(),
            str_random(),
            str_random(),
            str_random(),
        ];
        $inputData = [
            str_random(),
            str_random(),
            str_random(),
            str_random(),
        ];

        $restfulService = new EntityService(
            TestEntity::class,
            $this->restfulServiceFactoryMock,
            $this->repositoryMock,
            $this->getValidatorFactory(false, $inputData, $someRules),
            $this->connectionMock,
            $this->dispatcher
        );

        $this->expectException(ValidationException::class);
        $restfulService->validate($inputData, $someRules);
    }

    protected function getValidatorFactory(bool $success, array $data = [], array $rules = []): Factory
    {
        $validatorMock = \Mockery::mock(Validator::class);
        $validatorMock->shouldReceive('fails')->andReturn(!$success);
        $validationFactory = \Mockery::mock(Factory::class);
        $validationFactory->shouldReceive('make')->withArgs([$data, $rules])->andReturn($validatorMock);
        /** @var Factory $validationFactory */
        return $validationFactory;
    }
}
