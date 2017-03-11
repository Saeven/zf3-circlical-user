<?php

namespace CirclicalUser\Provider;

interface UserResetTokenInterface
{
    const STATUS_UNUSED = 0;

    const STATUS_INVALID = 9;

    const STATUS_USED = 1;

    public function setStatus(int $status);

    public function isValid(AuthenticationRecordInterface $authenticationRecord, string $checkToken, string $requestingIpAddress, bool $validateFingerprint, bool $validateIp): bool;

}