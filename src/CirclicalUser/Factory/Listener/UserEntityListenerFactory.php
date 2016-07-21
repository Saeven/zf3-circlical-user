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
     *
     * @return mixed
     * @throws \Exception
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $config = $config['circlical']['user'];

        if (!isset($config['entity'])) {
            throw new \Exception("You must specify the user Entity that CirclicalUser will use!");
        }

        return new UserEntityListener($config['entity']);
    }
}