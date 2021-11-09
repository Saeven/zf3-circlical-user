<?php

declare(strict_types=1);

namespace CirclicalUser\Mapper;

use CirclicalUser\Entity\UserResetToken;
use CirclicalUser\Provider\AuthenticationRecordInterface;
use CirclicalUser\Provider\UserResetTokenInterface;
use CirclicalUser\Provider\UserResetTokenProviderInterface;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UserResetTokenMapper extends AbstractDoctrineMapper implements UserResetTokenProviderInterface
{
    protected string $entityName = UserResetToken::class;

    /**
     * Get the count of requests in the last 5 minutes
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getRequestCount(AuthenticationRecordInterface $authenticationRecord): int
    {
        $fiveMinutesAgo = new DateTime('now', new DateTimeZone('UTC'));
        $fiveMinutesAgo->modify('-5 minutes');

        $query = $this->getRepository()->createQueryBuilder('r')
            ->select('COUNT(r.id) AS total')
            ->where('r.authentication = :authentication')
            ->andWhere('r.request_time > :since')
            ->setParameter('authentication', $authenticationRecord)
            ->setParameter('since', $fiveMinutesAgo)
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * Get the latest request
     */
    public function get(int $tokenId): ?UserResetTokenInterface
    {
        return $this->getRepository()->findOneBy(['id' => $tokenId, 'status' => UserResetTokenInterface::STATUS_UNUSED]);
    }

    /**
     * Modify previously created tokens that are not used, so that their status is invalid. There should only be one
     * valid token at any time.
     */
    public function invalidateUnusedTokens(AuthenticationRecordInterface $authenticationRecord): void
    {
        $query = $this->getRepository()->createQueryBuilder('r')
            ->update()
            ->set('r.status', UserResetTokenInterface::STATUS_INVALID)
            ->where('r.authentication = :authentication')
            ->andWhere('r.status = :status_unused')
            ->setParameters(
                [
                    'authentication' => $authenticationRecord,
                    'status_unused' => UserResetTokenInterface::STATUS_UNUSED,
                ]
            )
            ->getQuery();

        $query->execute();
    }
}
