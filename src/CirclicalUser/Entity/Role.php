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
     * @var Role
     * @ORM\ManyToOne(targetEntity="CirclicalUser\Entity\Role")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $parent;


    /**
     * Get the id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set the id.
     *
     * @param int $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = (int)$id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get the parent role
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set the parent role.
     *
     * @param Role $parent
     *
     * @return void
     */
    public function setParent(Role $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Return all inherited roles, including the start role, in this to-root traversal.
     * @return array
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