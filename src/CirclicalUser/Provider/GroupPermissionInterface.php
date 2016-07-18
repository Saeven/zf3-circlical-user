<?php

namespace CirclicalUser\Provider;


/**
 * Interface GroupPermissionInterface
 *
 * This defines a permission that's granted to a role, as opposed to a permission that's granted to a user.
 *
 * @package CirclicalUser\Provider
 */
interface GroupPermissionInterface
{
    public function getResourceClass() : string;

    public function getResourceId();

    public function getRole();

    public function getActions() : array;

    public function addAction($action);

    public function removeAction($action);

}