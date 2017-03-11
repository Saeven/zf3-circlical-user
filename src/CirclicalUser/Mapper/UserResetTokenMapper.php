<?php

namespace CirclicalUser\Mapper;

use CirclicalUser\Entity\UserResetToken;
use CirclicalUser\Provider\AuthenticationRecordInterface;
use CirclicalUser\Provider\UserResetTokenProviderInterface;


/**
 * Class UserResetTokenMapper
 *
 * @package CirclicalUser\Mapper
 */
class UserResetTokenMapper extends AbstractDoctrineMapper implements UserResetTokenProviderInterface
{
    protected $entityName = UserResetToken::class;

    /**
     * Get the count of requests in the last 5 minutes
     *
     * @param AuthenticationRecordInterface $authenticationRecord
     *
     * @return int
     */
    public function getRequestCount(AuthenticationRecordInterface $authenticationRecord): int
    {
        $fiveMinutesAgo = new \DateTime('now', new \DateTimeZone('UTC'));
        $fiveMinutesAgo->modify('-5 minutes');

        $query = $this->getRepository()->createQueryBuilder('r')
            ->select('COUNT(*) AS total')
            ->where('r.authentication = :authentication')
            ->andWhere('r.request_time > :since')
            ->setParameter('authentication', $authenticationRecord)
            ->setParameter('since', $fiveMinutesAgo)
            ->getQuery();

        return $query->getSingleScalarResult();
    }


    /**
     * Get the latest request
     *
     * @param int $tokenId
     *
     * @return mixed
     */
    public function get(int $tokenId)
    {
        return $this->getRepository()->findOneBy(['id' => $tokenId, 'status' => UserResetTokenProviderInterface::STATUS_UNUSED]);
    }
}