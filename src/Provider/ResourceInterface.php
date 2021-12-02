<?php

declare(strict_types=1);

namespace CirclicalUser\Provider;

/**
 * Interface ResourceInterface
 *
 * The crux of the resource system's Object functionality.  You'll need to make your resources classes implement this
 * if you want it to be governed by the permission system this library provides.
 */
interface ResourceInterface
{
    public function getClass(): string;

    public function getId(): string;
}
