<?php

namespace CirclicalUser\Provider;

use CirclicalUser\Entity\UserResetToken;

interface UserResetTokenProviderInterface
{

    const STATUS_UNUSED = 0;

    const STATUS_INVALID = 9;

    const STATUS_USED = 1;


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
     *
     * @param int $tokenId
     *
     * @return UserResetTokenInterface
     */
    public function get(int $tokenId);
}