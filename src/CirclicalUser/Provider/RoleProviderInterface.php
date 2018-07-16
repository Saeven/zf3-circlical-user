<?php

namespace CirclicalUser\Provider;

/**
 * Interface RoleProviderInterface
 * @package CirclicalUser\Provider
 *
 * Your role provider will need to implement this interface.  The role provider is pluggable, configured through your
 * module configuration.
 */
interface RoleProviderInterface
{
    /**
     * Fetch an array of all RoleInterface objects
     * @return RoleInterface[]
     */
    public function getAllRoles(): array;


    /**
     * Fetch a role with a particular name
     *
     * @param $name
     *
     * @return RoleInterface
     */
    public function getRoleWithName(string $name);

}
