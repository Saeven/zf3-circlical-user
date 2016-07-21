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
        $userConfig = $config['circlical']['user'];

        $userProvider = $userConfig['providers']['user'] ?? UserMapper::class;
        $authMapper = $userConfig['providers']['auth'] ?? AuthenticationMapper::class;

        return new AuthenticationService(
            $serviceLocator->get($authMapper),
            $serviceLocator->get($userProvider),
            base64_decode($userConfig['auth']['crypto_key']),
            $userConfig['auth']['transient'],
            false
        );
    }
}