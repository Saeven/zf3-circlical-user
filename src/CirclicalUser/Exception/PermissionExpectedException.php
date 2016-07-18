<?php

namespace CirclicalUser\Exception;

class PermissionExpectedException extends \Exception
{
    public function __construct($expected, $got)
    {
        parent::__construct("Expected to work with a rule of type $expected, but got $got instead. Fix your rule provider.");
    }
}