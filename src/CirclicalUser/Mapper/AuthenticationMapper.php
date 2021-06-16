<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Entity\Authentication;
use CirclicalUser\Provider\AuthenticationProviderInterface;
use CirclicalUser\Provider\AuthenticationRecordInterface;
use CirclicalUser\Provider\UserInterface;

class AuthenticationMapper extends AbstractDoctrineMapper implements AuthenticationProviderInterface
{
    protected $entityName = Authentication::class;

    public function findByUsername(string $username): ?AuthenticationRecordInterface
    {
        return $this->getRepository()->findOneBy(['username' => $username]);
    }

    public function findByUserId($userId): ?AuthenticationRecordInterface
    {
        return $this->getRepository()->createQueryBuilder('a')
            ->select('a')
            ->where('a.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleResult();
    }

    public function create(UserInterface $user, string $username, string $hash, string $rawKey): AuthenticationRecordInterface
    {
        return new Authentication($user, $username, $hash, base64_encode($rawKey));
    }
}
