<?php

namespace CirclicalUser\Factory\Controller;

use CirclicalUser\Controller\CliController;
use CirclicalUser\Mapper\GroupPermissionMapper;
use CirclicalUser\Mapper\RoleMapper;
use CirclicalUser\Mapper\UserMapper;
use CirclicalUser\Mapper\UserPermissionMapper;
use CirclicalUser\Service\AccessService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class CliControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $userConfig = $config['circlical']['user'];

        $userProvider = isset($userConfig['providers']['user']) ? $userConfig['providers']['user'] : UserMapper::class;
        $roleProvider = isset($userConfig['providers']['role']) ? $userConfig['providers']['role'] : RoleMapper::class;
        $groupRuleProvider = isset($userConfig['providers']['rule']['group']) ? $userConfig['providers']['rule']['group'] : GroupPermissionMapper::class;
        $userRuleProvider = isset($userConfig['providers']['rule']['user']) ? $userConfig['providers']['rule']['user'] : UserPermissionMapper::class;

        return new CliController(
            $container->get($userProvider),
            $container->get($roleProvider),
            $container->get($groupRuleProvider),
            $container->get($userRuleProvider),
            $container->get(AccessService::class)
        );
    }
}