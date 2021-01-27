<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Entity\Authentication;
use CirclicalUser\Provider\AuthenticationProviderInterface;
use CirclicalUser\Provider\AuthenticationRecordInterface;

class AuthenticationMapper extends AbstractDoctrineMapper implements AuthenticationProviderInterface
{
    protected $entityName = Authentication::class;

    public function findByUsername(string $username): ?AuthenticationRecordInterface
    {
        return $this->getRepository()->findOneBy(['username' => $username]);
    }

    public function findByUserId($userId): ?AuthenticationRecordInterface
    {
        return $this->getRepository()->findOneBy(['user_id' => $userId]);
    }

    public function create($userId, string $username, string $hash, string $rawKey): AuthenticationRecordInterface
    {
        return new Authentication($userId, $username, $hash, base64_encode($rawKey));
    }
}
