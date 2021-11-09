<?php

declare(strict_types=1);

namespace CirclicalUser\Mapper;

use CirclicalUser\Entity\UserPermission;
use CirclicalUser\Provider\ResourceInterface;
use CirclicalUser\Provider\UserInterface;
use CirclicalUser\Provider\UserPermissionInterface;
use CirclicalUser\Provider\UserPermissionProviderInterface;
use Doctrine\ORM\NonUniqueResultException;

class UserPermissionMapper extends AbstractDoctrineMapper implements UserPermissionProviderInterface
{
    protected string $entityName = UserPermission::class;

    /**
     * Get any user-level, string (simple) permissions that are configured in the database.
     *
     * @throws NonUniqueResultException
     */
    public function getUserPermission(string $string, UserInterface $user): ?UserPermissionInterface
    {
        $query = $this->getRepository()->createQueryBuilder('r')
            ->select('r')
            ->where('r.resource_class = :resourceClass AND r.resource_id=:resourceId AND r.user=:user')
            ->setParameter('resourceClass', 'string')
            ->setParameter('resourceId', $string)
            ->setParameter('user', $user)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * Get resource-type permissions from the database
     *
     * @throws NonUniqueResultException
     */
    public function getResourceUserPermission(ResourceInterface $resource, UserInterface $user): ?UserPermissionInterface
    {
        $query = $this->getRepository()->createQueryBuilder('r')
            ->select('r')
            ->where('r.resource_class = :resourceClass AND r.resource_id=:resourceId AND r.user=:user')
            ->setParameter('resourceClass', $resource->getClass())
            ->setParameter('resourceId', $resource->getId())
            ->setParameter('user', $user)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * Create a user permission, not persisted, and return it.
     */
    public function create(UserInterface $user, string $resourceClass, string $resourceId, array $actions): UserPermissionInterface
    {
        return new UserPermission($user, $resourceClass, $resourceId, $actions);
    }
}
