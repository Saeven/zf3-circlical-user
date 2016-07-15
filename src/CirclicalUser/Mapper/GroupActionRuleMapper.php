<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Provider\GroupActionRuleInterface;
use CirclicalUser\Provider\GroupActionRuleProviderInterface;
use CirclicalUser\Provider\ResourceInterface;
use CirclicalUser\Provider\UserActionRuleInterface;

class GroupActionRuleMapper extends AbstractDoctrineMapper implements GroupActionRuleProviderInterface
{
    protected $entityName = 'CirclicalUser\Entity\ActionRule';

    /**
     * @param $string
     *
     * @return GroupActionRuleInterface[]
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
     * @return array|\CirclicalUser\Provider\GroupActionRuleInterface[]
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

    public function create() : GroupActionRuleInterface
    {
    }
}