<?php

declare(strict_types=1);

namespace CirclicalUser\Mapper;

use CirclicalUser\Entity\Role;
use CirclicalUser\Provider\RoleInterface;
use CirclicalUser\Provider\RoleProviderInterface;

/**
 * Get and put roles out of the database
 */
class RoleMapper extends AbstractDoctrineMapper implements RoleProviderInterface
{
    protected string $entityName = Role::class;

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
