<?php

namespace CirclicalUser\Exception;

use Throwable;

class PrivilegeEscalationException extends \Exception
{
    public function __construct()
    {
        parent::__construct("For security reasons, the super-admin role cannot be granted via the library. It must be injected through other means.");
    }
}
