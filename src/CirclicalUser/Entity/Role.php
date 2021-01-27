<?php

namespace CirclicalUser\Entity;

use CirclicalUser\Provider\RoleInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * An example entity that represents a role.
 *
 * @ORM\Entity
 * @ORM\Table(name="acl_roles", indexes={@ORM\Index(name="name_idx", columns={"name"})})
 *
 */
class Role implements RoleInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    private $name;

    /**
     * @var RoleInterface
     * @ORM\ManyToOne(targetEntity="CirclicalUser\Entity\Role")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $parent;

    public function __construct(string $name, ?RoleInterface $parent)
    {
        $this->name = $name;
        $this->parent = $parent;
    }


    public function getId(): int
    {
        return $this->id;
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
