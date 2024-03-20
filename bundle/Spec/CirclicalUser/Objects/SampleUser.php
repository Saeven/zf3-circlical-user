<?php

namespace Spec\CirclicalUser\Objects;

use CirclicalUser\Provider\AuthenticationRecordInterface;
use CirclicalUser\Provider\RoleInterface;
use CirclicalUser\Provider\UserInterface;

class SampleUser implements UserInterface
{
    private int $id;

    /**@var RoleInterface[] */
    private array $roles;

    private string $email;

    private string $name;

    /** @var ?AuthenticationRecordInterface */
    private $authenticationRecord;

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

    public function getId(): int|string|null
    {
        return $this->id;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function addRole(RoleInterface $role): void
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

    public function setAuthenticationRecord(AuthenticationRecordInterface $authentication): void
    {
        $this->authenticationRecord = $authentication;
    }

    public function getAuthenticationRecord(): ?AuthenticationRecordInterface
    {
        return $this->authenticationRecord;
    }
}



