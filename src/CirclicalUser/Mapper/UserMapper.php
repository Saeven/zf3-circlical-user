<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Provider\UserInterface as User;
use CirclicalUser\Provider\UserProviderInterface;

/**
 * Class UserMapper
 *
 * A UserMapper that'll use whatever User Entity you pass in through your config, if you choose to use the Doctrine
 * Entity system that this library provides.  Of course, you can also create your own Providers.
 *
 * @package CirclicalUser\Mapper
 */
class UserMapper extends AbstractDoctrineMapper implements UserProviderInterface
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