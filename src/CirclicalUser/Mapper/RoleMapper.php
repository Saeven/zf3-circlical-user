<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Provider\RoleInterface;
use CirclicalUser\Provider\RoleProviderInterface;
use CirclicalUser\Provider\UserInterface;

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


    /**
     * Fetch a role with a particular name
     *
     * @param $name
     *
     * @return mixed
     */
    public function getRoleWithName($name)
    {
        return $this->getRepository()->findOneBy(['name' => $name]);
    }
}