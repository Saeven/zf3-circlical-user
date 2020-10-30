<?php

namespace CirclicalUser\Provider;


/**
 * Interface UserPermissionProviderInterface
 *
 * Framework for your user-permission provider.
 *
 * @package CirclicalUser\Provider
 */
interface UserPermissionProviderInterface
{

    /**
     * @param               $string
     * @param UserInterface $user
     *
     * @return UserPermissionInterface
     */
    public function getUserPermission($string, UserInterface $user);


    /**
     * @param ResourceInterface $resource
     * @param UserInterface     $user
     *
     * @return UserPermissionInterface
     */
    public function getResourceUserPermission(ResourceInterface $resource, UserInterface $user);


    public function update($rule);


    public function create(UserInterface $user, $resourceClass, $resourceId, array $actions): UserPermissionInterface;


    public function save($rule);
}