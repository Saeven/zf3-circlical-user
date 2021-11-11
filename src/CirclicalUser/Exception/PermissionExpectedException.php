<?php

declare(strict_types=1);

namespace CirclicalUser\Exception;

use Exception;

class PermissionExpectedException extends Exception
{
    public function __construct(string $expected, string $got)
    {
        parent::__construct("Expected to work with a rule of type $expected, but got $got instead. Fix your rule provider.");
    }
}
