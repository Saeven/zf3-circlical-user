<?php

namespace Spec\CirclicalUser\Objects;

use CirclicalUser\Provider\AuthenticationRecordInterface;
use CirclicalUser\Provider\RoleInterface;
use CirclicalUser\Provider\UserInterface;

class SampleUser implements UserInterface
{
    private int $id;

    /**
     * @var RoleInterface[]
     */
    private array $roles;

    private string $email;

    private string $name;

    private ?AuthenticationRecordInterface $authenticationRecord;

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

    public function hasRole(RoleInterface $role): bool
    {
        foreach ($this->roles as $userRole) {
            if ($userRole === $role) {
                return true;
            }
        }

        return false;
    }

    public function hasRoleWithName(string $roleName): bool
    {
        foreach ($this->roles as $userRole) {
            if ($userRole->getName() === $roleName) {
                return true;
            }
        }

        return false;
    }

    public function setAuthenticationRecord(?AuthenticationRecordInterface $authentication): void
    {
        $this->authenticationRecord = $authentication;
    }

    public function getAuthenticationRecord(): ?AuthenticationRecordInterface
    {
        return $this->authenticationRecord;
    }
}



