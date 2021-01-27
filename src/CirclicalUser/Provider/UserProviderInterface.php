<?php

namespace CirclicalUser\Provider;

interface UserProviderInterface
{
    /**
     * Persist an updated entity
     */
    public function update($entity);


    /**
     * Find a user by email
     */
    public function findByEmail(string $email): ?UserInterface;


    /**
     * Get a user by ID
     */
    public function getUser($userId): ?UserInterface;
}
