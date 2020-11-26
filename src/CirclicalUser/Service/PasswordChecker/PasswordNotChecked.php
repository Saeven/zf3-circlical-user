<?php

namespace CirclicalUser\Service\PasswordChecker;

use CirclicalUser\Provider\PasswordCheckerInterface;
use CirclicalUser\Provider\UserInterface;

class PasswordNotChecked implements PasswordCheckerInterface
{
    public function isStrongPassword(string $clearPassword, ?UserInterface $user, array $options): bool
    {
        return true;
    }
}
