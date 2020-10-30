<?php

namespace CirclicalUser\Entity;

use CirclicalUser\Provider\AuthenticationRecordInterface;
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
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false, length=10, options={"unsigned"=true})
     */
    protected $user_id;


    /**
     * @var string
     * @ORM\Column(type="string", unique=true, nullable=false, length=254)
     */
    protected $username;


    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, length=255)
     */
    protected $hash;


    /**
     * @var string
     * A base64-encoded representation of the user's session key
     * @ORM\Column(type="string", nullable=true, length=192, options={"fixed" = true})
     */
    protected $session_key;


    /**
     * @var string
     * @ORM\Column(type="string", nullable=true, length=32, options={"fixed" = true})
     */
    protected $reset_hash;


    /**
     * @var string
     * @ORM\Column(type="datetime", nullable=true )
     */
    protected $reset_expiry;


    public function __construct(int $userId, string $username, string $hash, string $encodedSessionKey)
    {
        $this->user_id = $userId;
        $this->username = $username;
        $this->hash = $hash;
        $this->session_key = $encodedSessionKey;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
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
    public function setSessionKey(string $sessionKey)
    {
        $this->session_key = $sessionKey;
    }

    /**
     * Instead of setting a bas64-encoded string, you can set the raw bytes for the key.
     * This setter will base64-encode.
     */
    public function setRawSessionKey(string $sessionKey)
    {
        $this->session_key = base64_encode($sessionKey);
    }

    /**
     * @return string
     */
    public function getResetHash()
    {
        return $this->reset_hash;
    }

    /**
     * @param string $reset_hash
     */
    public function setResetHash($reset_hash)
    {
        $this->reset_hash = $reset_hash;
    }

    /**
     * @return string
     */
    public function getResetExpiry()
    {
        return $this->reset_expiry;
    }

    /**
     * @param string $reset_expiry
     */
    public function setResetExpiry($reset_expiry)
    {
        $this->reset_expiry = $reset_expiry;
    }
}