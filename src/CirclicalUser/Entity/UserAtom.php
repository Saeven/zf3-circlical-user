<?php

namespace CirclicalUser\Entity;

use CirclicalUser\Provider\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * CirclicalUser\Entity\UserAtom
 *
 * @ORM\Entity
 * @ORM\Table(name="users_atoms",indexes={
 *    @ORM\Index(name="lookup_idx", columns={"key", "value"}),
 * });
 */
class UserAtom
{
    /**
     * @var UserInterface
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="CirclicalUser\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;


    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=255, name="`key`")
     */
    private $key;


    /**
     * @var string
     * @ORM\Column(type="string", length=255, name="`value`")
     */
    private $value;


    public function __construct(UserInterface $user, string $key, string $value)
    {
        $this->user = $user;
        $this->key = $key;
        $this->value = $value;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
