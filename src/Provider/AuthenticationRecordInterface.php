<?php

declare(strict_types=1);

namespace CirclicalUser\Provider;

interface AuthenticationRecordInterface
{
    public function setUsername(string $usernameOrEmail): void;

    public function getSessionKey(): string;

    public function getRawSessionKey(): string;

    public function setSessionKey(string $sessionKey): void;

    public function setRawSessionKey(string $rawKey): void;

    public function getUsername(): string;

    public function getHash(): string;

    public function setHash(string $hash): void;

    public function getUserId(): int|string;

    public function getUser(): UserInterface;
}
