<?php

namespace CirclicalUser\Provider;

/**
 * Interface RoleInterface
 *
 * A user role, with a parent.
 *
 * @package CirclicalUser\Provider
 */
interface RoleInterface
{
    public function getId() : int;

    public function getName() : string;

    public function getParent();
}