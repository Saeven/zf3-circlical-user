<?php

declare(strict_types=1);

namespace CirclicalUser\Exception;

use Exception;

class UnknownResourceTypeException extends Exception
{
    public function __construct(string $class = "")
    {
        parent::__construct("Class $class does not implement ResourceInterface");
    }
}
