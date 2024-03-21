<?php

declare(strict_types=1);

namespace CirclicalUser\Entity;

use CirclicalUser\Provider\UserInterface;
use CirclicalUser\Provider\UserPermissionInterface;
use Doctrine\ORM\Mapping as ORM;

use function array_diff;
use function in_array;

/**
 * Similar to a standard action rule, that is role-based -- this one is user-based.
 * Used in cases where roles don't fit.
 */
#[ORM\Entity, ORM\Table(name: "acl_actions_users")]
class UserPermission implements UserPermissionInterface
{
    #[ORM\Id, ORM\Column(type: "integer", options: ['unsigned' => true]), ORM\GeneratedValue(strategy: "AUTO")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    protected string $resource_class;

    #[ORM\Column(type: "string", length: 255)]
    protected string $resource_id;

    /** @psalm-suppress ArgumentTypeCoercion */
    #[ORM\ManyToOne(targetEntity: 'CirclicalUser\Entity\User')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected UserInterface $user;

    #[ORM\Column(type: 'array')]
    protected array $actions;

    public function __construct(UserInterface $user, string $resourceClass, string $resourceId, array $actions)
    {
        $this->user = $user;
        $this->resource_class = $resourceClass;
        $this->resource_id = $resourceId;
        $this->actions = $actions;
    }

    public function getResourceClass(): string
    {
        return $this->resource_class;
    }

    public function getResourceId(): string
    {
        return $this->resource_id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getActions(): array
    {
        if (!$this->actions) {
            return [];
        }

        return $this->actions;
    }

    public function addAction(string $action): void
    {
        if (!$this->actions) {
            $this->actions = [];
        }
        if (in_array($action, $this->actions, true)) {
            return;
        }
        $this->actions[] = $action;
    }

    public function removeAction(string $action): void
    {
        if (!$this->actions) {
            return;
        }
        $this->actions = array_diff($this->actions, [$action]);
    }

    public function can(string $actionName): bool
    {
        if (!$this->actions) {
            return false;
        }

        return in_array($actionName, $this->actions, true);
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
