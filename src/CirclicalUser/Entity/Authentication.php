<?php

namespace CirclicalUser\Entity;

use CirclicalUser\Provider\AuthenticationRecordInterface;
use CirclicalUser\Provider\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Void_;

/**
 * CirclicalUser\Entity\Authentication
 *
 * @ORM\Entity
 * @ORM\Table(name="users_auth", indexes={@ORM\Index(name="username_idx", columns={"username"})})
 *
 */
class Authentication implements AuthenticationRecordInterface
{
    /**
     * @var UserInterface
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="CirclicalUser\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;


    /**
     * @var string
     * @ORM\Column(type="string", unique=true, nullable=false, length=254)
     */
    private $username;


    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, length=255)
     */
    private $hash;


    /**
     * @var string
     * A base64-encoded representation of the user's session key
     * @ORM\Column(type="string", nullable=true, length=192, options={"fixed" = true})
     */
    private $session_key;


    /**
     * @var string
     * @ORM\Column(type="string", nullable=true, length=32, options={"fixed" = true})
     */
    private $reset_hash;


    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable", nullable=true)
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

    public function getResetExpiry(): \DateTimeImmutable
    {
        return $this->reset_expiry;
    }

    public function setResetExpiry(\DateTimeImmutable $dateTime): void
    {
        $this->reset_expiry = $dateTime;
    }

    public function getUserId()
    {
        return $this->getUser()->getId();
    }
}
