<?php

namespace CirclicalUser\Provider;

use CirclicalUser\Entity\UserResetToken;

interface UserResetTokenProviderInterface
{
    /**
     * Persist
     *
     * @param UserResetTokenInterface $entity
     *
     * @return mixed
     */
    public function save($entity);


    /**
     * Update
     *
     * @param UserResetTokenInterface $entity
     *
     * @return mixed
     */
    public function update($entity);

    /**
     * Get the count of requests in the last 5 minutes
     *
     * @param AuthenticationRecordInterface $authenticationRecord
     *
     * @return int
     */
    public function getRequestCount(AuthenticationRecordInterface $authenticationRecord): int;


    /**
     * Get the latest request
     */
    public function get(int $tokenId): ?UserResetTokenInterface;


    /**
     * Modify previously created tokens that are not used, so that their status is invalid. There should only be one
     * valid token at any time.
     *
     * @param AuthenticationRecordInterface $authenticationRecord
     *
     * @return mixed
     */
    public function invalidateUnusedTokens(AuthenticationRecordInterface $authenticationRecord);
}