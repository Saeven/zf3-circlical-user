<?php

namespace CirclicalUser\Factory\Service;

use CirclicalUser\Mapper\GroupPermissionMapper;
use CirclicalUser\Mapper\RoleMapper;
use CirclicalUser\Mapper\UserMapper;
use CirclicalUser\Mapper\UserPermissionMapper;
use CirclicalUser\Service\AccessService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use CirclicalUser\Service\AuthenticationService;

class AccessServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $userConfig = $config['circlical']['user'];

        if (!isset($userConfig['guards'])) {
            throw new \Exception("You don't have any guards set up! Please follow the steps in the readme.  Define an empty guards definition to get rid of this error.");
        }

        $guards = $userConfig['guards'] ?? [];
        $userProvider = isset($userConfig['providers']['user']) ? $userConfig['providers']['user'] : UserMapper::class;
        $roleProvider = isset($userConfig['providers']['role']) ? $userConfig['providers']['role'] : RoleMapper::class;
        $groupRuleProvider = isset($userConfig['providers']['rule']['group']) ? $userConfig['providers']['rule']['group'] : GroupPermissionMapper::class;
        $userRuleProvider = isset($userConfig['providers']['rule']['user']) ? $userConfig['providers']['rule']['user'] : UserPermissionMapper::class;

        $accessService = new AccessService(
            $guards,
            $container->get($roleProvider),
            $container->get($groupRuleProvider),
            $container->get($userRuleProvider),
            $container->get($userProvider)
        );

        $authenticationService = $container->get(AuthenticationService::class);
        $user = $authenticationService->getIdentity();

        if ($user) {
            $accessService->setUser($user);
        }

        return $accessService;
    }
}