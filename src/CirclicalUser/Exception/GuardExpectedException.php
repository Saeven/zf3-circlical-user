<?php

namespace CirclicalUser\Exception;

class GuardExpectedException extends \Exception
{
    public function __construct($controllerName)
    {
        parent::__construct("No rules are configured for guard $controllerName.");
    }

}