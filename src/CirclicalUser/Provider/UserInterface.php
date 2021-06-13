<?php

namespace CirclicalUser\Provider;

interface UserInterface
{
    public function getId();

    public function getRoles();

    public function getEmail();

    /**
     * When you implement this, strongly consider adding your own guard to ensure that the role being added,
     * is NOT the super-admin role.  Preventing privilege escalation is important if you enable super-admins
     * via configuration.
     */
    public function addRole(RoleInterface $role);

    public function hasRoleWithName(string $roleName): bool;

    public function hasRole(RoleInterface $roleName): bool;

    public function setAuthenticationRecord(AuthenticationRecordInterface $authentication): void;

    public function getAuthenticationRecord(): ?AuthenticationRecordInterface;
}
