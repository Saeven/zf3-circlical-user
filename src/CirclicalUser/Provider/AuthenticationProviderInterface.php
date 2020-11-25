<?php

namespace CirclicalUser\Provider;

interface AuthenticationProviderInterface
{
    /**
     * Find a proper authentication record by username. Note, usernames
     * could be anything, including email addresses.
     *
     * @return AuthenticationRecordInterface
     */
    public function findByUsername(string $username): ?AuthenticationRecordInterface;


    /**
     * Find an  authentication record by user ID.
     */
    public function findByUserId($userId): ?AuthenticationRecordInterface;


    /**
     * Update an auth record (e.g., for password rehash)
     *
     * @param AuthenticationRecordInterface $record
     *
     * @return mixed
     */
    public function update($record);


    /**
     * Save an auth record
     *
     * @param AuthenticationRecordInterface $record
     *
     * @return mixed
     */
    public function save($record);

    public function create($userId, string $username, string $hash, string $rawKey): AuthenticationRecordInterface;

}