<?php

declare(strict_types=1);

namespace CirclicalUser\Provider;

/**
 * Framework for your user-permission provider.
 */
interface UserPermissionProviderInterface
{
    public function getUserPermission(string $string, UserInterface $user): ?UserPermissionInterface;

    public function getResourceUserPermission(ResourceInterface $resource, UserInterface $user): ?UserPermissionInterface;

    public function create(UserInterface $user, string $resourceClass, string $resourceId, array $actions): UserPermissionInterface;

    public function save(object $entity): void;

    public function update(object $entity): void;
}
