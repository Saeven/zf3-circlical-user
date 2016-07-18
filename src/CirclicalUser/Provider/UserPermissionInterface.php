<?php

namespace CirclicalUser\Provider;


/**
 * Interface UserPermissionInterface
 *
 * This interface defines a user-level permission that supersedes what this user may otherwise have as a function of
 * its group permissions.
 *
 * @package CirclicalUser\Provider
 */
interface UserPermissionInterface
{
    public function getResourceClass() : string;

    public function getResourceId();

    public function getUser();

    public function getActions() : array;

    public function addAction($action);

    public function removeAction($action);

}