<?php

namespace CirclicalUser;

use CirclicalUser\Listener\AccessListener;
use Doctrine\DBAL\Types\Type;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
use Laminas\Console\Console;
use Laminas\Mvc\MvcEvent;

class Module
{
    protected static $isConsole;

    public static function isConsole(): bool
    {
        if (null === static::$isConsole) {
            static::$isConsole = (PHP_SAPI === 'cli');
        }

        return static::$isConsole;
    }

    public static function overrideIsConsole($flag): void
    {
        if (null !== $flag) {
            $flag = (bool)$flag;
        }
        static::$isConsole = $flag;
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
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

//            try {
//                $remote = new RemoteAddress;
//                $remote->setUseProxy(true);
//                $mapper = $serviceManager->get(UserAuthenticationLogMapper::class);
//                $logEntity = new UserAuthenticationLog(
//                    $authEvent->getIdentity(),
//                    new \DateTime("now"),
//                    $remote->getIpAddress()
//                );
//                $mapper->save($logEntity);
//
//            } catch (\Exception $x) {
//
//            }
//        });
    }
}
