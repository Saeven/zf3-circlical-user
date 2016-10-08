<?php

namespace CirclicalUser\Factory\Listener;

use CirclicalUser\Listener\UserEntityListener;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class UserEntityListenerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $config = $config['circlical']['user']['doctrine'];

        if (!isset($config['entity'])) {
            throw new \Exception("You must specify the user Entity that CirclicalUser will use!");
        }

        return new UserEntityListener($config['entity']);
    }
}