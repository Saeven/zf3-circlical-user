<?php

namespace CirclicalUser\Exception;

use CirclicalUser\Provider\ResourceInterface;
use CirclicalUser\Provider\RoleInterface;

class ExistingAccessException extends \Exception
{
    public function __construct(RoleInterface $role, ResourceInterface $resource, $action, $existingRole)
    {
        parent::__construct("Access for '{$role->getName()}' to '$action' '{$resource->getClass()}' with ID '{$resource->getId()}'  is already granted by the '$existingRole' role.");
    }
}