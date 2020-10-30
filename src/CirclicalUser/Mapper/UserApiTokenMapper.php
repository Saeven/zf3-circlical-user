<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Entity\UserApiToken;
use Ramsey\Uuid\Doctrine\UuidBinaryType;


/**
 * Class UserApiTokenMapper
 *
 * @package CirclicalUser\Mapper
 */
class UserApiTokenMapper extends AbstractDoctrineMapper
{
    protected $entityName = UserApiToken::class;

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