<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Entity\GroupPermission;
use CirclicalUser\Provider\GroupPermissionInterface;
use CirclicalUser\Provider\GroupPermissionProviderInterface;
use CirclicalUser\Provider\ResourceInterface;
use CirclicalUser\Provider\RoleInterface;

class GroupPermissionMapper extends AbstractDoctrineMapper implements GroupPermissionProviderInterface
{
    protected $entityName = 'CirclicalUser\Entity\ActionRule';

    /**
     * @param $string
     *
     * @return GroupPermissionInterface[]
     */
    public function getStringActions($string) : array
    {
        $query = $this->getRepository()->createQueryBuilder('r')
            ->select('r')
            ->where('r.resource_class = "string" AND r.resource_id=:resourceId')
            ->setParameter('resourceId', $string)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param ResourceInterface $resource
     *
     * @return array|\CirclicalUser\Provider\GroupPermissionInterface[]
     */
    public function getResourceActions(ResourceInterface $resource) : array
    {
        $query = $this->getRepository()->createQueryBuilder('r')
            ->select('r')
            ->where('r.resource_class = :resourceClass AND r.resource_id=:resourceId')
            ->setParameter('resourceClass', $resource->getClass())
            ->setParameter('resourceId', $resource->getId())
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