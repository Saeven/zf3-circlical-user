<?php

namespace CirclicalUser\Factory\Service;

use CirclicalUser\Mapper\GroupPermissionMapper;
use CirclicalUser\Mapper\RoleMapper;
use CirclicalUser\Mapper\UserMapper;
use CirclicalUser\Mapper\UserPermissionMapper;
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

        $userProvider = $userConfig['providers']['user'] ?? UserMapper::class;
        $roleProvider = $userConfig['providers']['role'] ?? RoleMapper::class;
        $groupRuleProvider = $userConfig['providers']['rule']['group'] ?? GroupPermissionMapper::class;
        $userRuleProvider = $userConfig['providers']['rule']['user'] ?? UserPermissionMapper::class;

        $accessService = new AccessService(
            $guards,
            $serviceLocator->get($roleProvider),
            $serviceLocator->get($groupRuleProvider),
            $serviceLocator->get($userRuleProvider),
            $serviceLocator->get($userProvider)
        );

        $authenticationService = $serviceLocator->get(AuthenticationService::class);
        $user = $authenticationService->getIdentity();

        if ($user) {
            $accessService->setUser($user);
        }

        return $accessService;
    }
}