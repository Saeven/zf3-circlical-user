<?php

namespace Spec\CirclicalUser\Objects;

use CirclicalUser\Provider\RoleInterface;
use CirclicalUser\Provider\UserInterface;

class SampleUser implements UserInterface
{
    private $id;
    private $roles;
    private $email;
    private $name;

    public function __construct(int $id, array $roles, string $email, string $name)
    {
        $this->id = $id;
        $this->roles = $roles;
        $this->email = $email;
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function addRole(RoleInterface $role)
    {
        $this->roles[] = $role;
    }
}



