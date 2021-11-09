<?php

declare(strict_types=1);

namespace CirclicalUser\Mapper;

use CirclicalUser\Entity\Authentication;
use CirclicalUser\Provider\AuthenticationProviderInterface;
use CirclicalUser\Provider\AuthenticationRecordInterface;
use CirclicalUser\Provider\UserInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

use function base64_encode;

class AuthenticationMapper extends AbstractDoctrineMapper implements AuthenticationProviderInterface
{
    protected string $entityName = Authentication::class;

    public function findByUsername(string $username): ?AuthenticationRecordInterface
    {
        return $this->getRepository()->findOneBy(['username' => $username]);
    }

    /**
     * @param mixed $userId
     *
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
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
