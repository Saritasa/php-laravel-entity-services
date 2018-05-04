<?php

namespace Saritasa\LaravelEntityServices\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Base entity event.
 */
abstract class EntityEvent
{
    use Dispatchable;

    /**
     * Entity with which some action was performed.
     *
     * @var Model
     */
    protected $entity;

    /**
     * Entity class.
     *
     * @var string
     */
    protected $modelClass;

    /**
     * EntityEvent constructor.
     *
     * @param Model $entity Entity with which some action was performed.
     */
    public function __construct(Model $entity)
    {
        $this->entity = $entity;
        $this->modelClass = get_class($entity);
    }

    /**
     * Get entity with which some action was performed.
     *
     * @return Model
     */
    public function getEntity(): Model
    {
        return $this->entity;
    }

    /**
     * Get entity class.
     *
     * @return string
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }
}
