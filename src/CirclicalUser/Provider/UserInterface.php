<?php

namespace CirclicalUser\Provider;

interface UserInterface
{
    public function getId();

    public function getRoles();

    public function getEmail();

    public function addRole(RoleInterface $role);

    public function hasRoleWithName(string $roleName): bool;

    public function hasRole(RoleInterface $roleName): bool;
}