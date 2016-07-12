<?php

namespace CirclicalUser\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * An example entity that represents an action rule.
 *
 * @ORM\Entity
 * @ORM\Table(name="acl_actions_users")
 *
 */
class UserActionRule
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="CirclicalUser\Entity\ActionRule", inversedBy="user_exceptions")
     * @ORM\JoinColumn(name="action_rule_id", referencedColumnName="id")
     */
    protected $action_rule;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="CirclicalUser\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

}