<?php

namespace Saritasa\LaravelEntityServices\Events;

/**
 * Dispatched after delete operation.
 */
class EntityDeletedEvent
{
    /**
     * Deleted model class.
     *
     * @var string
     */
    protected $modelClass;

    /**
     * Deleted model id.
     *
     * @var string
     */
    protected $id;

    /**
     * Dispatched after delete operation.
     *
     * @param string $modelClass Deleted model class
     * @param string $id Deleted model id
     */
    public function __construct(string $modelClass, string $id)
    {
        $this->modelClass = $modelClass;
        $this->id = $id;
    }

    /**
     * Deleted model class.
     *
     * @return string
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    /**
     * Deleted model id.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
