<?php

namespace CirclicalUser\Provider;

interface AuthenticationRecordInterface
{

    public function setUsername($usernameOrEmail);

    public function getSessionKey(): string;

    public function getRawSessionKey(): string;

    public function setSessionKey(string $sessionKey);

    public function setRawSessionKey(string $rawKey);

    public function getUsername(): string;

    public function getUserId();

    public function getHash(): string;

    public function setHash(string $hash);
}
