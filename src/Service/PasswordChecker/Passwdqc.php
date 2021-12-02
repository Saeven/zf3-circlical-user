<?php

declare(strict_types=1);

namespace CirclicalUser\Service\PasswordChecker;

use CirclicalUser\Provider\PasswordCheckerInterface;

class Passwdqc implements PasswordCheckerInterface
{
    private ?array $creationOptions;

    public function __construct(?array $creationOptions = null)
    {
        $this->creationOptions = $creationOptions;
    }

    public function isStrongPassword(string $clearPassword, array $userData): bool
    {
        return (new \ParagonIE\Passwdqc\Passwdqc())->check($clearPassword);
    }
}
