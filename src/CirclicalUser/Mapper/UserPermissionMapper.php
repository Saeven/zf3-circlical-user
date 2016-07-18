<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Entity\UserPermission;
use CirclicalUser\Provider\ResourceInterface;
use CirclicalUser\Provider\UserPermissionInterface;
use CirclicalUser\Provider\UserPermissionProviderInterface;
use CirclicalUser\Provider\UserInterface;

class UserPermissionMapper extends AbstractDoctrineMapper implements UserPermissionProviderInterface
{
    protected $entityName = 'CirclicalUser\Entity\UserActionRule';

    public function getUserStringActions($string, UserInterface $user) : array
    {
        $query = $this->getRepository()->createQueryBuilder('r')
            ->select('r')
            ->where('r.resource_class = "string" AND r.resource_id=:resourceId')
            ->setParameter('resourceId', $string)
            ->getQuery();

        return $query->getResult();
    }

    public function getUserResourceActions(ResourceInterface $resource, UserInterface $user) : array
    {
        $query = $this->getRepository()->createQueryBuilder('r')
            ->select('r')
            ->where('r.resource_class = :resourceClass AND r.resource_id=:resourceId')
            ->setParameter('resourceClass', $resource->getClass())
            ->setParameter('resourceId', $resource->getId())
            ->getQuery();

        return $query->getResult();
    }

    public function create(UserInterface $user, $resourceClass, $resourceId, array $actions) : UserPermissionInterface
    {
        return new UserPermission($user, $resourceClass, $resourceId, $actions);
    }
}