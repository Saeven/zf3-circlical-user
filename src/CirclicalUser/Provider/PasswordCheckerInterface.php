<?php

namespace CirclicalUser\Provider;

use CirclicalUser\Provider\UserInterface as User;

interface PasswordCheckerInterface
{
    public function isStrongPassword(string $clearPassword, ?User $user, array $options): bool;
}