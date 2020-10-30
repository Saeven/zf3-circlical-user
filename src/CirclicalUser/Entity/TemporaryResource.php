<?php

namespace CirclicalUser\Entity;

use CirclicalUser\Provider\ResourceInterface;

class TemporaryResource implements ResourceInterface
{
    private $class;

    private $id;

    public function __construct($class, $id)
    {
        $this->class = $class;
        $this->id = $id;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getId(): string
    {
        return $this->id;
    }
}

