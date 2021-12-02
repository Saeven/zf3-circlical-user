<?php

declare(strict_types=1);

namespace CirclicalUser\Exception;

use Exception;

class GuardConfigurationException extends Exception
{
    public function __construct(string $controllerName, string $issue)
    {
        parent::__construct("An error occurred parsing your guard configuration for $controllerName, $issue");
    }
}
