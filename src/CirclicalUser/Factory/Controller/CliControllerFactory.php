<?php

namespace CirclicalUser\Factory\Controller;

use CirclicalUser\Controller\CliController;
use CirclicalUser\Mapper\GroupPermissionMapper;
use CirclicalUser\Mapper\RoleMapper;
use CirclicalUser\Mapper\UserMapper;
use CirclicalUser\Mapper\UserPermissionMapper;
use CirclicalUser\Service\AccessService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CliControllerFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();
        $config = $serviceLocator->get('config');
        $userConfig = $config['circlical']['user'];

        $userProvider = isset($userConfig['providers']['user']) ? $userConfig['providers']['user'] : UserMapper::class;
        $roleProvider = isset($userConfig['providers']['role']) ? $userConfig['providers']['role'] : RoleMapper::class;
        $groupRuleProvider = isset($userConfig['providers']['rule']['group']) ? $userConfig['providers']['rule']['group'] : GroupPermissionMapper::class;
        $userRuleProvider = isset($userConfig['providers']['rule']['user']) ? $userConfig['providers']['rule']['user'] : UserPermissionMapper::class;

        return new CliController(
            $serviceLocator->get($userProvider),
            $serviceLocator->get($roleProvider),
            $serviceLocator->get($groupRuleProvider),
            $serviceLocator->get($userRuleProvider),
            $serviceLocator->get(AccessService::class)
        );
    }
}