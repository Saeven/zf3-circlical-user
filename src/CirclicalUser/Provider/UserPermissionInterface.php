<?php

declare(strict_types=1);

namespace CirclicalUser\Provider;

/**
 * Interface UserPermissionInterface
 *
 * This interface defines a user-level permission that supersedes what this user may otherwise have as a function of
 * its group permissions.
 */
interface UserPermissionInterface
{
    public function getResourceClass(): string;

    public function getResourceId(): string;

    public function getUser(): UserInterface;

    public function can(string $actionName): bool;

    public function getActions(): array;

    public function addAction(string $action): void;

    public function removeAction(string $action): void;
}
