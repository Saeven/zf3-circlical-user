<?php

namespace CirclicalUser;

use CirclicalUser\Listener\AccessListener;
use Doctrine\DBAL\Types\Type;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
use Laminas\Console\Console;
use Laminas\Mvc\MvcEvent;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    public function onBootstrap(MvcEvent $mvcEvent)
    {
        if (!Type::hasType('uuid_binary')) {
            Type::addType('uuid_binary', UuidBinaryType::class);
        }

        if (Console::isConsole()) {
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
