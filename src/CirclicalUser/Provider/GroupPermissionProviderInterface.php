<?php

namespace CirclicalUser\Provider;

interface GroupPermissionProviderInterface
{

    /**
     * @param $string
     *
     * @return GroupPermissionInterface[]
     */
    public function getStringActions($string) : array;

    /**
     * @param ResourceInterface $resource
     *
     * @return array|GroupPermissionInterface[]
     */
    public function getResourceActions(ResourceInterface $resource) : array;


    public function update($rule);


    public function create(RoleInterface $role, $resourceClass, $resourceId, array $actions) : GroupPermissionInterface;


    public function save($rule);

}