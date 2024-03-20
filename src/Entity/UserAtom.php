<?php

declare(strict_types=1);

namespace CirclicalUser\Entity;

use CirclicalUser\Provider\UserInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity, ORM\Table(name:"users_atoms"), ORM\Index(fields: ['key', 'value'], name: "lookup_idx")]
class UserAtom
{
    /** @psalm-suppress ArgumentTypeCoercion */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: 'CirclicalUser\Entity\User')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private UserInterface $user;

    #[ORM\Id]
    #[ORM\Column(type: "string", length: 255, name: '`key`')]
    private string $key;

    #[ORM\Column(type: "string", length: 255, name: '`value`')]
    private string $value;

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
