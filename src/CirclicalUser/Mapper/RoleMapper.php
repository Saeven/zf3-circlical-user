<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Provider\RoleProviderInterface;

/**
 * Class RoleMapper
 *
 * Get and put roles out of the database
 *
 * @package CirclicalUser\Mapper
 */
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