<?php

namespace CirclicalUser\Factory\Service;

use CirclicalUser\Service\AccessService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use CirclicalUser\Service\AuthenticationService;

class AccessServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $userConfig = $config['circlical']['user'];
        $guards = $userConfig['guards'];

        $roleProvider = $userConfig['providers']['role'];
        $ruleProvider = $userConfig['providers']['rule'];

        $accessService = new AccessService(
            $guards,
            $serviceLocator->get($roleProvider),
            $serviceLocator->get($ruleProvider)
        );

        $authenticationService = $serviceLocator->get(AuthenticationService::class);
        $user = $authenticationService->getIdentity();

        if ($user) {
            $accessService->setUser($user);
        }

        return $accessService;
    }
}