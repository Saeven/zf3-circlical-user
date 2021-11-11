<?php

declare(strict_types=1);

namespace CirclicalUser\Service\PasswordChecker;

use CirclicalUser\Provider\PasswordCheckerInterface;

class Zxcvbn implements PasswordCheckerInterface
{
    private array $creationOptions;

    public function __construct(array $creationOptions)
    {
        $this->creationOptions = $creationOptions;
    }

    /**
     * Check strength using the excellent zxcvbn library.
     */
    public function isStrongPassword(string $clearPassword, array $userData): bool
    {
        $requiredStrength = $this->creationOptions['required_strength'] ?? 4;
        $strength = (new \ZxcvbnPhp\Zxcvbn())->passwordStrength($clearPassword, $userData);

        return $strength['score'] >= $requiredStrength;
    }
}
