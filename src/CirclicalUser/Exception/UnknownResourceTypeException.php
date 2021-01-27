<?php

namespace CirclicalUser\Exception;

class UnknownResourceTypeException extends \Exception
{
    public function __construct($class = "")
    {
        parent::__construct("Class $class does not implement ResourceInterface");
    }
}
