<?php

namespace CirclicalUser\Factory\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use CirclicalUser\Service\AuthenticationService;
use CirclicalUser\Mapper\AuthenticationMapper;
use CirclicalUser\Mapper\UserMapper;

class AuthenticationServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $config = $config['circlical']['user']['auth'];

        return new AuthenticationService(
            $serviceLocator->get(AuthenticationMapper::class),
            $serviceLocator->get(UserMapper::class),
            base64_decode($config['crypto_key']),
            $config['transient'],
            false
        );
    }
}