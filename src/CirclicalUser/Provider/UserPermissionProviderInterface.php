<?php

namespace CirclicalUser\Provider;


interface UserPermissionProviderInterface
{

    /**
     * @param               $string
     * @param UserInterface $user
     *
     * @return UserPermissionInterface
     */
    public function getUserStringActions($string, UserInterface $user);


    /**
     * @param ResourceInterface $resource
     * @param UserInterface     $user
     *
     * @return UserPermissionInterface
     */
    public function getUserResourceActions(ResourceInterface $resource, UserInterface $user);


    public function update($rule);


    public function create(UserInterface $user, $resourceClass, $resourceId, array $actions) : UserPermissionInterface;


    public function save($rule);
}