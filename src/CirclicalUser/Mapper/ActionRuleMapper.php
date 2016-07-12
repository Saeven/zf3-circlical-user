<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Provider\ActionRuleInterface;
use CirclicalUser\Provider\ActionRuleProviderInterface;
use CirclicalUser\Provider\ResourceInterface;

class ActionRuleMapper extends AbstractDoctrineMapper implements ActionRuleProviderInterface
{
    protected $entityName = 'CirclicalUser\Entity\ActionRule';

    /**
     * @param $string
     * @return ActionRuleInterface[]
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
     * @return array|\CirclicalUser\Provider\ActionRuleInterface[]
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
}