<?php

namespace Saritasa\LaravelEntityServices\Exceptions;

use Saritasa\LaravelEntityServices\Contracts\IEntityServiceFactory;

/**
 * Throws in case bad class binding in Service managers factories.
 *
 * @see IEntityServiceFactory
 */
class EntityServiceBindingException extends EntityServiceException
{
    /**
     * Throws in case bad class binding in Service managers factories.
     *
     * @param string $expectedClass Expected class
     * @param string $givenClass Actual class
     */
    public function __construct(string $expectedClass, string $givenClass)
    {
        parent::__construct(trans(
            'entity_services.inheritance_error',
            ['expectedClass' => $expectedClass, 'givenClass' => $givenClass]
        ));
    }
}
