<?php

namespace CirclicalUser\Service\PasswordChecker;

use CirclicalUser\Provider\PasswordCheckerInterface;
use CirclicalUser\Provider\UserInterface;

class Zxcvbn implements PasswordCheckerInterface
{
    /**
     * Check strength using the excellent zxcvbn library.
     */
    public function isStrongPassword(string $clearPassword, ?UserInterface $user, array $options): bool
    {
        $requiredStrength = $options['required_strength'] ?? 4;
        $userData = array_values(array_filter($user ? array_values((array)$user) : [], 'is_string'));
        $strength = (new \ZxcvbnPhp\Zxcvbn())->passwordStrength($clearPassword, $userData);

        return $strength['score'] >= $requiredStrength;
    }
}
