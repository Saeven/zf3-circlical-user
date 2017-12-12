<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Entity\Authentication;
use CirclicalUser\Provider\AuthenticationProviderInterface;
use CirclicalUser\Provider\AuthenticationRecordInterface;


class AuthenticationMapper extends AbstractDoctrineMapper implements AuthenticationProviderInterface
{
    protected $entityName = Authentication::class;

    /**
     * @param $username
     *
     * @return null|Authentication
     */
    public function findByUsername($username)
    {
        return $this->getRepository()->findOneBy(['username' => $username]);
    }

    /**
     * @param $userId
     *
     * @return null|Authentication
     */
    public function findByUserId($userId)
    {
        return $this->getRepository()->findOneBy(['user_id' => $userId]);
    }

    /**
     * @param $userId
     * @param $username
     * @param $hash
     * @param $rawKey
     *
     * @return AuthenticationRecordInterface
     */
    public function create($userId, $username, $hash, $rawKey): AuthenticationRecordInterface
    {
        return new Authentication($userId, $username, $hash, $rawKey);
    }
}