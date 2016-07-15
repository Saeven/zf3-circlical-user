<?php

namespace CirclicalUser\Entity;

use CirclicalUser\Provider\GroupActionRuleInterface;
use CirclicalUser\Provider\UserActionRuleInterface;
use CirclicalUser\Provider\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Similar to a standard action rule, that is role-based -- this one is user-based.
 * Used in cases where roles don't fit.
 *
 * @ORM\Entity
 * @ORM\Table(name="acl_actions_users")
 *
 */
class UserActionRule implements UserActionRuleInterface
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
     * @ORM\ManyToOne(targetEntity="CirclicalUser\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;


    /**
     * @var array
     * @ORM\Column(type="array")
     */
    protected $actions;


    public function __construct(UserInterface $user, $resourceClass, $resourceId, array $actions)
    {
        $this->user = $user;
        $this->resource_class = $resourceClass;
        $this->resource_id = $resourceId;
        $this->actions = $actions;
    }


    public function getResourceClass()
    {
        return $this->resource_class;
    }

    public function getResourceId()
    {
        return $this->resource_id;
    }

    public function getUser()
    {
        return $this->user;
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
}