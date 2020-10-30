<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Entity\UserPermission;
use CirclicalUser\Provider\ResourceInterface;
use CirclicalUser\Provider\UserPermissionInterface;
use CirclicalUser\Provider\UserPermissionProviderInterface;
use CirclicalUser\Provider\UserInterface;

/**
 * Class UserPermissionMapper
 * @package CirclicalUser\Mapper
 */
class UserPermissionMapper extends AbstractDoctrineMapper implements UserPermissionProviderInterface
{
    protected $entityName = UserPermission::class;

    /**
     * Get any user-level, string (simple) permissions that are configured in the database.
     *
     * @param               $string
     * @param UserInterface $user
     *
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getUserPermission($string, UserInterface $user)
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
     * @param ResourceInterface $resource
     * @param UserInterface     $user
     *
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getResourceUserPermission(ResourceInterface $resource, UserInterface $user)
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
     *
     * @param UserInterface $user
     * @param               $resourceClass
     * @param               $resourceId
     * @param array         $actions
     *
     * @return UserPermissionInterface
     */
    public function create(UserInterface $user, $resourceClass, $resourceId, array $actions): UserPermissionInterface
    {
        return new UserPermission($user, $resourceClass, $resourceId, $actions);
    }
}
