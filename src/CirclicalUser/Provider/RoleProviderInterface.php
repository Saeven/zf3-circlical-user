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
    public function getAllRoles() : array;
}