<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Provider\UserInterface as User;

/**
 * Class UserMapper
 *
 * A UserMapper that'll use whatever User Entity you pass in through your config, if you choose to use the Doctrine
 * Entity system that this library provides.  Of course, you can also create your own Providers.
 *
 * @package CirclicalUser\Mapper
 */
class UserMapper extends AbstractDoctrineMapper
{
    protected $entityName;

    public function __construct( $entityName )
    {
        $this->entityName = $entityName;
    }

    /**
     * @param $userId
     *
     * @return User
     */
    public function getUser($userId)
    {
        return $this->getRepository()->findOneBy(['id' => $userId]);
    }

    /**
     * Locate a user by email address
     *
     * @param $email
     *
     * @return User
     */
    public function findByEmail($email)
    {
        return $this->getRepository()->findOneBy(['email' => $email]);
    }


    /**
     * Get all users with a particular company ID
     *
     * @param $companyId
     *
     * @return User[]
     */
    public function getUsersInCompany($companyId)
    {
        return $this->getRepository()->findBy(['company_id' => $companyId]);
    }

    /**
     * Find users whose first names or last names start with a given substring
     *
     * @param $startWith
     *
     * @return mixed
     */
    public function getUsersLike($startWith)
    {
        $query = $this->getRepository()
            ->createQueryBuilder('u')
            ->select('u.id, u.first_name, u.last_name')
            ->where('u.first_name LIKE :search OR u.last_name LIKE :search')
            ->setParameter('search', "{$startWith}%")
            ->orderBy('u.last_name')
            ->getQuery();

        return $query->getArrayResult();
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

}