<?php

declare(strict_types=1);

namespace CirclicalUser\Entity;

use CirclicalUser\Provider\AuthenticationRecordInterface;
use CirclicalUser\Provider\UserInterface;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

use function base64_decode;
use function base64_encode;

#[ORM\Entity, ORM\Table(name: 'users_auth')]
#[ORM\Index(name: 'username_idx', columns: ['username'])]
class Authentication implements AuthenticationRecordInterface
{
    /** @psalm-suppress ArgumentTypeCoercion */
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: 'CirclicalUser\Entity\User', inversedBy: 'authenticationRecord')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private UserInterface $user;

    #[ORM\Column(type: "string", length: 254, unique: true, nullable: false)]
    private string $username;

    #[ORM\Column(type: "string", length: 255, nullable: false)]
    private string $hash;

    #[ORM\Column(type: "string", length: 192, nullable: true, options: ['fixed' => true])]
    private string $session_key;

    #[ORM\Column(type: "string", length: 32, nullable: true, options: ['fixed' => true])]
    private ?string $reset_hash;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?DateTimeImmutable $reset_expiry;

    public function __construct(UserInterface $user, string $username, string $hash, string $encodedSessionKey)
    {
        $this->user = $user;
        $this->username = $username;
        $this->hash = $hash;
        $this->session_key = $encodedSessionKey;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $usernameOrEmail): void
    {
        $this->username = $usernameOrEmail;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function getSessionKey(): string
    {
        return $this->session_key;
    }

    public function getRawSessionKey(): string
    {
        return base64_decode($this->session_key);
    }

    /**
     * Value gets Base64-encoded for storage
     */
    public function setSessionKey(string $sessionKey): void
    {
        $this->session_key = $sessionKey;
    }

    /**
     * Instead of setting a bas64-encoded string, you can set the raw bytes for the key.
     * This setter will base64-encode.
     */
    public function setRawSessionKey(string $rawKey): void
    {
        $this->session_key = base64_encode($rawKey);
    }

    public function getResetHash(): ?string
    {
        return $this->reset_hash;
    }

    public function setResetHash(string $resetHash): void
    {
        $this->reset_hash = $resetHash;
    }

    public function getResetExpiry(): ?DateTimeImmutable
    {
        return $this->reset_expiry;
    }

    public function setResetExpiry(DateTimeImmutable $dateTime): void
    {
        $this->reset_expiry = $dateTime;
    }

    public function getUserId(): int|string
    {
        return $this->getUser()->getId();
    }
}
