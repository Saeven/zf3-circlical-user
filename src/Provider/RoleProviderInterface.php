<?php

declare(strict_types=1);

namespace CirclicalUser\Provider;

/**
 * Your role provider will need to implement this interface.  The role provider is pluggable, configured through your
 * module configuration.
 */
interface RoleProviderInterface
{
    /**
     * Fetch an array of all RoleInterface objects
     *
     * @return RoleInterface[]
     */
    public function getAllRoles(): array;

    /**
     * Fetch a role with a particular name
     */
    public function getRoleWithName(string $name): ?RoleInterface;
}
