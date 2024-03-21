<?php

declare(strict_types=1);

namespace CirclicalUser\Listener;

use CirclicalUser\Entity\Authentication;
use CirclicalUser\Entity\UserApiToken;
use CirclicalUser\Entity\UserAtom;
use CirclicalUser\Entity\UserAuthenticationLog;
use CirclicalUser\Entity\UserPermission;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

/**
 * Because this module doesn't provide a user, for use with Doctrine
 */
class UserEntityListener implements EventSubscriber
{
    public const DEFAULT_ENTITY = 'CirclicalUser\Entity\User';

    private string $userEntity;

    public function __construct(string $userEntity)
    {
        $this->userEntity = $userEntity;
    }

    public function getSubscribedEvents(): array
    {
        return ['loadClassMetadata'];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if ($this->userEntity === self::DEFAULT_ENTITY) {
            return;
        }

        switch ($classMetadata->getName()) {
            case UserPermission::class:
            case UserApiToken::class:
            case Authentication::class:
            case UserAtom::class:
            case UserAuthenticationLog::class:
                /** @psalm-suppress PropertyTypeCoercion */
                $classMetadata->associationMappings['user']['targetEntity'] = $this->userEntity;
                break;
        }
    }
}
