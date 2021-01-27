<?php

namespace CirclicalUser\Entity;

use CirclicalUser\Provider\ResourceInterface;

class TemporaryResource implements ResourceInterface
{
    private string $class;

    private string $id;

    public function __construct(string $class, string $id)
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
