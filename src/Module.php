<?php

declare(strict_types=1);

namespace CirclicalUser;

use CirclicalUser\Listener\AccessListener;
use Doctrine\DBAL\Types\Type;
use Laminas\Mvc\MvcEvent;
use Ramsey\Uuid\Doctrine\UuidBinaryType;

use const PHP_SAPI;

class Module
{
    protected static ?bool $isConsole = null;

    public static function isConsole(): bool
    {
        if (null === static::$isConsole) {
            static::$isConsole = PHP_SAPI === 'cli';
        }

        return static::$isConsole;
    }

    public static function overrideIsConsole(?bool $flag): void
    {
        static::$isConsole = $flag;
    }

    public function getConfig(): array
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function onBootstrap(MvcEvent $mvcEvent)
    {
        if (!Type::hasType('uuid_binary')) {
            Type::addType('uuid_binary', UuidBinaryType::class);
        }

        if (static::isConsole()) {
            return;
        }

        $application = $mvcEvent->getApplication();
        $serviceLocator = $application->getServiceManager();
        $strategy = $serviceLocator->get(AccessListener::class);
        $eventManager = $application->getEventManager();
        $strategy->attach($eventManager);
    }
}
