<?php

namespace CirclicalUser\Provider;

/**
 * Interface GroupPermissionProviderInterface
 *
 * This is truly a relative interface to UserPermissionProviderInterface.  The big difference, is that these group
 * permissions return arrays of permissions, since the roles are hierarchical.  User permissions, in contrast, are
 * necessarily indexed to a User, and will return but a singular permission when getPermission or getResourceUserPermission
 * are called.
 *
 * @see     CirclicalUser\Mapper\GroupPermissionMapper for a sample implementation
 *
 * @package CirclicalUser\Provider
 */
interface GroupPermissionProviderInterface
{
    /**
     * @return GroupPermissionInterface[]
     */
    public function getPermissions(string $string): array;

    /**
     * @param ResourceInterface $resource
     *
     * @return GroupPermissionInterface[]
     */
    public function getResourcePermissions(ResourceInterface $resource): array;


    /**
     * @return GroupPermissionInterface[]
     */
    public function getResourcePermissionsByClass(string $resourceClass): array;


    public function update($rule);


    public function create(RoleInterface $role, string $resourceClass, string $resourceId, array $actions): GroupPermissionInterface;


    public function save($rule);

}
