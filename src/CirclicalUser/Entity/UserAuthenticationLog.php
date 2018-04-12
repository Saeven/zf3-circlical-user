<?php

namespace CirclicalUser\Entity;

use CirclicalUser\Provider\UserInterface;
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
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"unsigned"=true});
     */
    private $user_id;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $auth_time;

    /**
     * @ORM\Column(type="string", nullable=true, length=16, options={"fixed"=true});
     */
    private $ip_address;


    public function __construct(UserInterface $user, \DateTimeImmutable $time, string $ipAddress)
    {
        $this->id = 0;
        $this->user_id = $user->getId();
        $this->auth_time = $time;
        $this->ip_address = $ipAddress;
    }
}