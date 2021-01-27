<?php

namespace CirclicalUser\Exception;

class GuardConfigurationException extends \Exception
{
    public function __construct(string $controllerName, string $issue)
    {
        parent::__construct("An error occurred parsing your guard configuration for $controllerName, $issue");
    }
}
