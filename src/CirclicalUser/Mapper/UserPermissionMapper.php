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
    protected $entityName = 'CirclicalUser\Entity\UserActionRule';

    /**
     * Get any user-level, string (simple) permissions that are configured in the database.
     *
     * @param               $string
     * @param UserInterface $user
     *
     * @return array
     */
    public function getUserPermission($string, UserInterface $user) : array
    {
        $query = $this->getRepository()->createQueryBuilder('r')
            ->select('r')
            ->where('r.resource_class = "string" AND r.resource_id=:resourceId')
            ->setParameter('resourceId', $string)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Get resource-type permissions from the database
     *
     * @param ResourceInterface $resource
     * @param UserInterface     $user
     *
     * @return array
     */
    public function getResourceUserPermission(ResourceInterface $resource, UserInterface $user) : array
    {
        $query = $this->getRepository()->createQueryBuilder('r')
            ->select('r')
            ->where('r.resource_class = :resourceClass AND r.resource_id=:resourceId')
            ->setParameter('resourceClass', $resource->getClass())
            ->setParameter('resourceId', $resource->getId())
            ->getQuery();

        return $query->getResult();
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
    public function create(UserInterface $user, $resourceClass, $resourceId, array $actions) : UserPermissionInterface
    {
        return new UserPermission($user, $resourceClass, $resourceId, $actions);
    }
}