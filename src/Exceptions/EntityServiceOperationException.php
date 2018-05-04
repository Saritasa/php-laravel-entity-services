<?php

namespace Saritasa\LaravelEntityServices\Exceptions;

use Saritasa\LaravelEntityServices\Contracts\IEntityService;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Throws when CRUD operation failed in entity service.
 *
 * @see IEntityService
 */
class EntityServiceOperationException extends EntityServiceException
{
    /**
     * Throws when CRUD operation failed in entity service.
     *
     * @param string $message Exception message
     * @param Throwable $previous Previous exception
     */
    public function __construct(string $message, Throwable $previous = null)
    {
        parent::__construct($message, Response::HTTP_INTERNAL_SERVER_ERROR, $previous);
    }
}
