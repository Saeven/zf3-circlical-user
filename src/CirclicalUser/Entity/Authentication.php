<?php

declare(strict_types=1);

namespace CirclicalUser\Entity;

use CirclicalUser\Provider\AuthenticationRecordInterface;
use CirclicalUser\Provider\UserInterface;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

use function base64_decode;
use function base64_encode;

/**
 * @ORM\Entity
 * @ORM\Table(name="users_auth", indexes={@ORM\Index(name="username_idx", columns={"username"})})
 */
class Authentication implements AuthenticationRecordInterface
{
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="CirclicalUser\Entity\User", inversedBy="authenticationRecord")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var UserInterface
     */
    private $user;

    /**
     * @ORM\Column(type="string", unique=true, nullable=false, length=254)
     *
     * @var string
     */
    private $username;

    /**
     * @ORM\Column(type="string", nullable=false, length=255)
     *
     * @var string
     */
    private $hash;

    /**
     * A base64-encoded representation of the user's session key
     *
     * @ORM\Column(type="string", nullable=true, length=192, options={"fixed" = true})
     *
     * @var string
     */
    private $session_key;

    /**
     * @ORM\Column(type="string", nullable=true, length=32, options={"fixed" = true})
     *
     * @var string
     */
    private $reset_hash;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     *
     * @var DateTimeImmutable
     */
    private $reset_expiry;

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

    public function setHash(string $hash)
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
    public function setRawSessionKey(string $sessionKey): void
    {
        $this->session_key = base64_encode($sessionKey);
    }

    public function getResetHash(): string
    {
        return $this->reset_hash;
    }

    public function setResetHash(string $resetHash): void
    {
        $this->reset_hash = $resetHash;
    }

    public function getResetExpiry(): DateTimeImmutable
    {
        return $this->reset_expiry;
    }

    public function setResetExpiry(DateTimeImmutable $dateTime): void
    {
        $this->reset_expiry = $dateTime;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->getUser()->getId();
    }
}
