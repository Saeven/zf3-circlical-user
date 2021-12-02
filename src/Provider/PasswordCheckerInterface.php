<?php

declare(strict_types=1);

namespace CirclicalUser\Provider;

interface PasswordCheckerInterface
{
    public function isStrongPassword(string $clearPassword, array $userData): bool;
}
