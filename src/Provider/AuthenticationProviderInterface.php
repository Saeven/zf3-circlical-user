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
     *
     * @param mixed $userId
     */
    public function findByUserId($userId): ?AuthenticationRecordInterface;

    public function create(UserInterface $user, string $username, string $hash, string $rawKey): AuthenticationRecordInterface;

    public function save(object $entity);

    public function update(object $entity);
}
