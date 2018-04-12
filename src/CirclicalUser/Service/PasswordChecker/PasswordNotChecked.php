<?php

namespace CirclicalUser\Service\PasswordChecker;

use CirclicalUser\Provider\PasswordCheckerInterface;

class PasswordNotChecked implements PasswordCheckerInterface
{
    public function isStrongPassword(string $clearPassword): bool
    {
        return true;
    }
}