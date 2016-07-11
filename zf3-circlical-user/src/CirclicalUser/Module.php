<?php

namespace CirclicalUser;

use CirclicalUser\Entity\UserAuthenticationLog;
use CirclicalUser\Listener\AccessListener;
use Zend\Console\Console;
use Zend\Mvc\MvcEvent;

class Module
{

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }


    public function onBootstrap(MvcEvent $mvcEvent)
    {
        if( Console::isConsole() ) {
            return;
        }

        $application = $mvcEvent->getApplication();
        $serviceLocator = $application->getServiceManager();
        $strategy = $serviceLocator->get(AccessListener::class);
        $eventManager = $application->getEventManager();
        $strategy->attach( $eventManager );


//        $sharedManager->attach('*', 'user.authenticate', function ($authEvent) use ($serviceManager) {
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