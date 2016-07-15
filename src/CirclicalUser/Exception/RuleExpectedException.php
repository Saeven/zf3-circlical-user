<?php

namespace CirclicalUser\Exception;

class RuleExpectedException extends \Exception
{
    public function __construct($expected, $got)
    {
        parent::__construct("Expected to work with a rule of type $expected, but got $got instead. Check your rule provider.");
    }
}