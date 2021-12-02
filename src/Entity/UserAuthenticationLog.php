<?php

declare(strict_types=1);

namespace CirclicalUser\Entity;

use CirclicalUser\Provider\UserInterface;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Log entity that you can use when users log in.
 *
 * @ORM\Entity
 * @ORM\Table(name="users_auth_logs")
 */
class UserAuthenticationLog
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="CirclicalUser\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var UserInterface
     */
    private $user;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     *
     * @var DateTimeImmutable
     */
    private $auth_time;

    /**
     * @ORM\Column(type="string", nullable=true, length=16, options={"fixed"=true});
     *
     * @var string
     */
    private $ip_address;

    public function __construct(UserInterface $user, DateTimeImmutable $time, string $ipAddress)
    {
        $this->id = 0;
        $this->user = $user;
        $this->auth_time = $time;
        $this->ip_address = $ipAddress;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getAuthTime(): DateTimeImmutable
    {
        return $this->auth_time;
    }

    public function getIpAddress(): string
    {
        return $this->ip_address;
    }
}
