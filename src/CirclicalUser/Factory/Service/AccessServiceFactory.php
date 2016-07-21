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

        $userProvider = isset($userConfig['providers']['user']) ? $userConfig['providers']['user'] : UserMapper::class;
        $roleProvider = isset($userConfig['providers']['role']) ? $userConfig['providers']['role'] : RoleMapper::class;
        $groupRuleProvider = isset($userConfig['providers']['rule']['group']) ? $userConfig['providers']['rule']['group'] : GroupPermissionMapper::class;
        $userRuleProvider = isset($userConfig['providers']['rule']['user']) ? $userConfig['providers']['rule']['user'] : UserPermissionMapper::class;

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