<?php

namespace Saritasa\LaravelEntityServices\Exceptions;

use Saritasa\LaravelEntityServices\Contracts\IEntityServiceFactory;

/**
 * Throws in case when can not register custom entity service.
 *
 * @see IEntityServiceFactory
 */
class EntityServiceRegisterException extends EntityServiceException
{

}
