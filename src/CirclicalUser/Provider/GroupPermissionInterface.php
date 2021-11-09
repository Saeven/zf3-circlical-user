<?php

declare(strict_types=1);

namespace CirclicalUser\Provider;

/**
 * Interface GroupPermissionInterface
 *
 * This defines a permission that's granted to a role, as opposed to a permission that's granted to a user.
 */
interface GroupPermissionInterface
{
    public function getResourceClass(): string;

    public function getResourceId(): string;

    public function getRole(): RoleInterface;

    public function can(string $actionName): bool;

    public function getActions(): array;

    public function addAction(string $action): void;

    public function removeAction(string $action): void;
}
