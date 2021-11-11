<?php

declare(strict_types=1);

namespace CirclicalUser\Mapper;

use CirclicalUser\Entity\Role;
use CirclicalUser\Provider\UserInterface;
use CirclicalUser\Provider\UserProviderInterface;

/**
 * A UserMapper that'll use whatever User Entity you pass in through your config, if you choose to use the Doctrine
 * Entity system that this library provides.  Of course, you can also create your own Providers.
 */
class UserMapper extends AbstractDoctrineMapper implements UserProviderInterface
{
    protected string $entityName;

    public function __construct(string $entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * @param mixed $userId
     */
    public function getUser($userId): ?UserInterface
    {
        return $this->getRepository()->findOneBy(['id' => $userId]);
    }

    /**
     * Locate a user by email address
     */
    public function findByEmail(string $email): ?UserInterface
    {
        return $this->getRepository()->findOneBy(['email' => $email]);
    }

    /**
     * Fetch a list of all users
     *
     * @return mixed
     */
    public function getAllUsers()
    {
        $query = $this->getRepository()->createQueryBuilder('u')
            ->select('u')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Get users with a specific role, and _not_ its hierarchical parents.
     * In other words, if you have 'admin' which inherits from 'standard' and you request 'admin',
     * you will not list 'standard' users.
     *
     * @return array
     */
    public function getUsersWithRole(Role $role): array
    {
        $query = $this->getRepository()->createQueryBuilder('u')
            ->select('u')
            ->where(':role MEMBER OF u.roles')
            ->setParameter('role', $role)
            ->getQuery();

        return $query->getResult() ?? [];
    }
}
