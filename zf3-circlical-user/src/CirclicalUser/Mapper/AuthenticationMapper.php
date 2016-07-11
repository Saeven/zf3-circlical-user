<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Entity\Authentication;


class AuthenticationMapper extends AbstractDoctrineMapper
{
    protected $entityName = 'CirclicalUser\Entity\Authentication';

    /**
     * @param $username
     * @return null|Authentication
     */
    public function findByUsername($username)
    {
        return $this->getRepository()->findOneBy(['username' => $username]);
    }

    /**
     * @param $userId
     * @return null|Authentication
     */
    public function findByUserId($userId)
    {
        return $this->getRepository()->findOneBy(['user_id' => $userId]);
    }

}