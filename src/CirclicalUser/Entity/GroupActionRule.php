<?php

namespace CirclicalUser\Entity;

use CirclicalUser\Provider\GroupActionRuleInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * An example entity that represents an action rule.
 *
 * @ORM\Entity
 * @ORM\Table(name="acl_actions")
 *
 */
class GroupActionRule implements GroupActionRuleInterface
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

    public function getActions()
    {
        return $this->actions;
    }
}
