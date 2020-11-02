<?php

namespace CirclicalUser\Entity;

use CirclicalUser\Provider\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * A password-reset token.  This is the thing that you would exchange in a forgot-password email
 * that the user can later consume to trigger a password change.
 *
 * @ORM\Entity
 * @ORM\Table(name="users_api_tokens")
 *
 */
class UserApiToken
{
    public const SCOPE_NONE = 0;

    use SecretIdPublicUuidTrait;

    /**
     * @var UserInterface
     * @ORM\ManyToOne(targetEntity="CirclicalUser\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    private $creation_time;


    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $last_used;


    /**
     * @var integer
     * @ORM\Column(type="integer", options={"default":0, "unsigned": true})
     */
    private $times_used;


    /**
     * @var integer
     * @ORM\Column(type="integer", options={"default":0, "unsigned": true})
     */
    private $scope;


    /**
     * UserApiToken constructor.
     *
     * @param UserInterface $user
     * @param int           $scope Push a bit-flag integer into this value to resolve scopes
     *
     * @throws \Exception
     */
    public function __construct(UserInterface $user, int $scope)
    {
        $this->user = $user;
        $this->creation_time = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->scope = $scope;
        $this->times_used = 0;
        $this->uuid = Uuid::uuid4();
    }

    public function addScope(int $newScope): void
    {
        $this->scope |= $newScope;
    }

    public function removeScope(int $removeScope): void
    {
        $this->scope &= ~$removeScope;
    }

    public function hasScope(int $checkForScope): bool
    {
        return ($this->scope & $checkForScope) === $checkForScope;
    }

    public function clearScope(): void
    {
        $this->scope = self::SCOPE_NONE;
    }

    public function getLastUsed(): ?\DateTimeImmutable
    {
        return $this->last_used;
    }

    public function getTimesUsed(): int
    {
        return $this->times_used;
    }

    public function tagUse(): void
    {
        $this->times_used++;
        $this->last_used = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getToken(): string
    {
        return $this->uuid;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
