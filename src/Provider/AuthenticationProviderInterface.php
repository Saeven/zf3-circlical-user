<?php

declare(strict_types=1);

namespace CirclicalUser\Provider;

interface AuthenticationProviderInterface
{
    /**
     * Find a proper authentication record by username. Note, usernames
     * could be anything, including email addresses.
     */
    public function findByUsername(string $username): ?AuthenticationRecordInterface;

    /**
     * Find an  authentication record by user ID.
     */
    public function findByUserId(mixed $userId): ?AuthenticationRecordInterface;

    public function create(UserInterface $user, string $username, string $hash, string $rawKey): AuthenticationRecordInterface;

    public function save(object $entity): void;

    public function update(object $entity): void;
}
