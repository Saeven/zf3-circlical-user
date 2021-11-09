<?php

declare(strict_types=1);

namespace CirclicalUser\Provider;

interface UserProviderInterface
{
    /**
     * Find a user by email
     */
    public function findByEmail(string $email): ?UserInterface;

    /**
     * Get a user by ID
     *
     * @param mixed $userId
     */
    public function getUser($userId): ?UserInterface;
}
