<?php

namespace CirclicalUser\Listener;

use CirclicalUser\Entity\UserApiToken;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\Common\EventSubscriber;
use CirclicalUser\Entity\UserPermission;

/**
 * Because this module doesn't provide a user
 *
 * Class UserEntityListener
 * @package CirclicalUser\Listener
 */
class UserEntityListener implements EventSubscriber
{
    const DEFAULT_ENTITY = 'CirclicalUser\Entity\User';

    private $userEntity;

    public function __construct($userEntity)
    {
        $this->userEntity = $userEntity;
    }

    public function getSubscribedEvents()
    {
        return ['loadClassMetadata'];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var \Doctrine\ORM\Mapping\ClassMetadata $classMetadata */
        $classMetadata = $eventArgs->getClassMetadata();

        if ($this->userEntity === self::DEFAULT_ENTITY) {
            return;
        }

        switch ($classMetadata->getName()) {
            case UserPermission::class:
            case UserApiToken::class:
                $classMetadata->associationMappings['user']['targetEntity'] = $this->userEntity;
                break;
        }
    }
}