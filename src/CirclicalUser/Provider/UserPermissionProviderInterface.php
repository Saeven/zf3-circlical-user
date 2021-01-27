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
    public function getUserPermission(string $string, UserInterface $user): ?UserPermissionInterface;

    public function getResourceUserPermission(ResourceInterface $resource, UserInterface $user): ?UserPermissionInterface;

    public function update($rule);

    public function create(UserInterface $user, string $resourceClass, string $resourceId, array $actions): UserPermissionInterface;

    public function save($rule);
}
