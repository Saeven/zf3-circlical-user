<?php

namespace CirclicalUser\Exception;

class InvalidRoleException extends \Exception
{
    public function __construct(string $roleName)
    {
        parent::__construct("No role with name $roleName exists.  You can only operate on valid roles.");
    }
}
