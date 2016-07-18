<?php

namespace CirclicalUser\Factory\Service;

use CirclicalUser\Mapper\UserMapper;
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
        $groupRuleProvider = $userConfig['providers']['rule']['group'];
        $userRuleProvider = $userConfig['providers']['rule']['user'] ?? null;

        $accessService = new AccessService(
            $guards,
            $serviceLocator->get($roleProvider),
            $serviceLocator->get($groupRuleProvider),
            $serviceLocator->get($userRuleProvider),
            $serviceLocator->get(UserMapper::class)
        );

        $authenticationService = $serviceLocator->get(AuthenticationService::class);
        $user = $authenticationService->getIdentity();

        if ($user) {
            $accessService->setUser($user);
        }

        return $accessService;
    }
}