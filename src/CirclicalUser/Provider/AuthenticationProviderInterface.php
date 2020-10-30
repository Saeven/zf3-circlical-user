<?php

namespace CirclicalUser\Provider;

interface AuthenticationProviderInterface
{
    /**
     * Find a proper authentication record by username. Note, usernames
     * could be anything, including email addresses.
     *
     * @param $username
     *
     * @return AuthenticationRecordInterface
     */
    public function findByUsername($username);


    /**
     * Find an  authentication record by user ID.
     *
     * @return AuthenticationRecordInterface
     */
    public function findByUserId($userId);


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

    /**
     * @param $userId
     * @param $username
     * @param $hash
     * @param $rawKey
     *
     * @return AuthenticationRecordInterface
     */
    public function create($userId, $username, $hash, $rawKey): AuthenticationRecordInterface;

}