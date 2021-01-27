<?php

namespace CirclicalUser\Exception;

use CirclicalUser\Provider\ResourceInterface;
use CirclicalUser\Provider\RoleInterface;

class ExistingAccessException extends \Exception
{
    public function __construct(RoleInterface $role, ResourceInterface $resource, string $action, string $existingRole)
    {
        parent::__construct(
            sprintf(
                "Access for '%s' to '%s' '%s' with ID '%s'  is already granted by the '%s' role.",
                $role->getName(),
                $action,
                $resource->getClass(),
                $resource->getId(),
                $existingRole
            )
        );
    }
}
