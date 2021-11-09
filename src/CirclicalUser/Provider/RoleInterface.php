<?php

declare(strict_types=1);

namespace CirclicalUser\Provider;

/**
 * A user role, with a parent.
 */
interface RoleInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getParent(): ?RoleInterface;
}
