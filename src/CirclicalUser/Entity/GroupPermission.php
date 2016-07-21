<?php

namespace CirclicalUser\Entity;

use CirclicalUser\Provider\GroupPermissionInterface;
use CirclicalUser\Provider\RoleInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * An example entity that represents an action rule.
 *
 * @ORM\Entity
 * @ORM\Table(name="acl_actions")
 *
 */
class GroupPermission implements GroupPermissionInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $resource_class;


    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $resource_id;


    /**
     * @var Role
     * @ORM\ManyToOne(targetEntity="CirclicalUser\Entity\Role")
     */
    protected $role;


    /**
     * @var array
     * @ORM\Column(type="array")
     */
    protected $actions;


    public function __construct(RoleInterface $role, $resourceClass, $resourceId, array $actions)
    {
        $this->role = $role;
        $this->resource_class = $resourceClass;
        $this->resource_id = $resourceId;
        $this->actions = $actions;
    }


    public function getResourceClass() : string
    {
        return $this->resource_class;
    }

    public function getResourceId()
    {
        return $this->resource_id;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getActions() : array
    {
        if (!$this->actions) {
            return [];
        }
        return $this->actions;
    }

    public function addAction($action)
    {
        if (!$this->actions) {
            $this->actions = [];
        }
        if (in_array($action, $this->actions)) {
            return;
        }
        $this->actions[] = $action;
    }

    public function removeAction($action)
    {
        if (!$this->actions) {
            return;
        }
        $this->actions = array_diff($this->actions, [$action]);
    }

    public function can($actionName) : bool
    {
        if (!$this->actions) {
            return false;
        }

        return in_array($actionName, $this->actions);
    }
}
