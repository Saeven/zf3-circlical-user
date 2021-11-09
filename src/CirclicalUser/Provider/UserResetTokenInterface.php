<?php

declare(strict_types=1);

namespace CirclicalUser\Provider;

interface UserResetTokenInterface
{
    public const STATUS_UNUSED = 0;
    public const STATUS_INVALID = 9;
    public const STATUS_USED = 1;

    public function setStatus(int $status);

    public function isValid(
        AuthenticationRecordInterface $authenticationRecord,
        string $checkToken,
        string $requestingIpAddress,
        bool $validateFingerprint,
        bool $validateIp
    ): bool;
}
