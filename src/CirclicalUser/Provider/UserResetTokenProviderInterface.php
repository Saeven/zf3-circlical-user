<?php

declare(strict_types=1);

namespace CirclicalUser\Provider;

interface UserResetTokenProviderInterface
{
    /**
     * Get the count of requests in the last 5 minutes
     */
    public function getRequestCount(AuthenticationRecordInterface $authenticationRecord): int;

    /**
     * Get the latest request
     */
    public function get(int $tokenId): ?UserResetTokenInterface;

    /**
     * Modify previously created tokens that are not used, so that their status is invalid. There should only be one
     * valid token at any time.
     */
    public function invalidateUnusedTokens(AuthenticationRecordInterface $authenticationRecord): void;
}
