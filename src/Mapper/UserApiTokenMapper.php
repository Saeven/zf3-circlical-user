<?php

declare(strict_types=1);

namespace CirclicalUser\Mapper;

use CirclicalUser\Entity\UserApiToken;
use Ramsey\Uuid\Doctrine\UuidBinaryType;

class UserApiTokenMapper extends AbstractDoctrineMapper
{
    protected string $entityName = UserApiToken::class;

    public function get(string $tokenStringRepresentation): ?UserApiToken
    {
        return $this->getRepository()->createQueryBuilder('uat')
            ->select('uat')
            ->where('uat.uuid = :token')
            ->setParameter('token', $tokenStringRepresentation, UuidBinaryType::NAME)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }
}
