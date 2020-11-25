<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Provider\RoleInterface;
use CirclicalUser\Provider\RoleProviderInterface;
use CirclicalUser\Provider\UserInterface;
use CirclicalUser\Entity\Role;

/**
 * Class RoleMapper
 *
 * Get and put roles out of the database
 *
 * @package CirclicalUser\Mapper
 */
class RoleMapper extends AbstractDoctrineMapper implements RoleProviderInterface
{
    protected $entityName = Role::class;

    public function getAllRoles(): array
    {
        $query = $this->getRepository()->createQueryBuilder('r')
            ->select('r')
            ->getQuery();

        return $query->getResult();
    }


    /**
     * Fetch a role with a particular name
     */
    public function getRoleWithName(string $name): ?RoleInterface
    {
        return $this->getRepository()->findOneBy(['name' => $name]);
    }
}