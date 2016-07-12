<?php

namespace CirclicalUser\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * An example entity that represents an action rule.
 *
 * @ORM\Entity
 * @ORM\Table(name="acl_actions")
 *
 */
class ActionRule
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


    /**
     * @ORM\OneToMany(targetEntity="CirclicalUser\Entity\UserActionRule", mappedBy="action_rule", cascade={"all"})
     */
    protected $user_exceptions;


    public function getResourceClass()
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

    public function getUserExceptions()
    {
        return $this->user_exceptions;
    }

}
