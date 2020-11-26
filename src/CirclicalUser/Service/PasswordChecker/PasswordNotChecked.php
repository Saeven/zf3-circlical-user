<?php

namespace CirclicalUser\Service\PasswordChecker;

use CirclicalUser\Provider\PasswordCheckerInterface;

class PasswordNotChecked implements PasswordCheckerInterface
{
    private $creationOptions;

    public function __construct(array $creationOptions = null)
    {
        $this->creationOptions = $creationOptions;
    }

    public function isStrongPassword(string $clearPassword, array $userData): bool
    {
        return true;
    }
}
