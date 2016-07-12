<?php

namespace CirclicalUser\Factory\Listener;

use CirclicalUser\Listener\AccessListener;
use CirclicalUser\Listener\UserEntityListener;
use CirclicalUser\Service\AccessService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserEntityListenerFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $config = $config['circlical']['user'];

        return new UserEntityListener( $config['providers']['user'] ?? null  );
    }
}