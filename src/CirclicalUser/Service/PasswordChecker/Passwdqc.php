<?php

namespace CirclicalUser\Service\PasswordChecker;

use CirclicalUser\Provider\PasswordCheckerInterface;

class Passwdqc implements PasswordCheckerInterface
{

    public function isStrongPassword(string $clearPassword): bool
    {
        $implementation = new \ParagonIE\Passwdqc\Passwdqc();

        return $implementation->check($clearPassword);
    }
}