<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Entity\GroupPermission;
use CirclicalUser\Provider\GroupPermissionInterface;
use CirclicalUser\Provider\GroupPermissionProviderInterface;
use CirclicalUser\Provider\ResourceInterface;
use CirclicalUser\Provider\RoleInterface;

class GroupPermissionMapper extends AbstractDoctrineMapper implements GroupPermissionProviderInterface
{
    protected $entityName = 'CirclicalUser\Entity\GroupPermission';

    /**
     * @param $string
     *
     * @return GroupPermissionInterface[]
     */
    public function getPermissions($string) : array
    {
        $query = $this->getRepository()->createQueryBuilder('r')
            ->select('r')
            ->where('r.resource_class = :resourceClass AND r.resource_id=:resourceId')
            ->setParameter('resourceClass', 'string')
            ->setParameter('resourceId', $string)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param ResourceInterface $resource
     *
     * @return array|\CirclicalUser\Provider\GroupPermissionInterface[]
     */
    public function getResourcePermissions(ResourceInterface $resource) : array
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
     * @param $resourceClass
     *
     * @return array
     */
    public function getResourcePermissionsByClass($resourceClass) : array
    {
        $query = $this->getRepository()->createQueryBuilder('r')
            ->select('r')
            ->where('r.resource_class = :resourceClass')
            ->setParameter('resourceClass', $resourceClass)
            ->getQuery();

        return $query->getResult();
    }

    public function create(RoleInterface $role, $resourceClass, $resourceId, array $actions) : GroupPermissionInterface
    {
        return new GroupPermission(
            $role,
            $resourceClass,
            $resourceId,
            $actions
        );
    }
}
