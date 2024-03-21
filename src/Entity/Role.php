<?php

declare(strict_types=1);

namespace CirclicalUser\Entity;

use CirclicalUser\Provider\RoleInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity, ORM\Table(name: "acl_roles"), ORM\Index(fields: ['name'], name: "name_idx")]
class Role implements RoleInterface
{
    #[ORM\Id, ORM\Column(type: "integer"), ORM\GeneratedValue(strategy: "AUTO")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255, unique: true, nullable: true)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: self::class), ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?RoleInterface $parent = null;

    public function __construct(string $name, ?RoleInterface $parent)
    {
        $this->name = $name;
        $this->parent = $parent;
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    /**
     * Probably shouldn't be used, but in case some folks have weird edge conditions, I'll leave it.
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get the parent role
     */
    public function getParent(): ?RoleInterface
    {
        return $this->parent;
    }

    /**
     * Set the parent role.
     */
    public function setParent(?RoleInterface $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * Return all inherited roles, including the start role, in this to-root traversal.
     */
    public function getInheritanceList(): array
    {
        $roleList = [$this];
        $role = $this;
        while ($parentRole = $role->getParent()) {
            $roleList[] = $parentRole;
            $role = $parentRole;
        }

        return $roleList;
    }
}
