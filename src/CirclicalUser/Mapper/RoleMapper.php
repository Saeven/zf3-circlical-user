<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Provider\RoleProviderInterface;

class RoleMapper extends AbstractDoctrineMapper implements RoleProviderInterface
{
    protected $entityName = 'CirclicalUser\Entity\Role';

    public function getAllRoles() : array
    {
        $query = $this->getRepository()->createQueryBuilder('r')
            ->select('r')
                ->getQuery();

        return $query->getResult();
    }
}