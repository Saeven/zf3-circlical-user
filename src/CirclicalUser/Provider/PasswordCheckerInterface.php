<?php

namespace CirclicalUser\Provider;

interface PasswordCheckerInterface
{
    public function isStrongPassword(string $clearPassword, array $userData): bool;
}
