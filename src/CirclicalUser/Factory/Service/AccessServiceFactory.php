<?php

namespace CirclicalUser\Factory\Service;

use CirclicalUser\Mapper\GroupPermissionMapper;
use CirclicalUser\Mapper\RoleMapper;
use CirclicalUser\Mapper\UserMapper;
use CirclicalUser\Mapper\UserPermissionMapper;
use CirclicalUser\Service\AccessService;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use CirclicalUser\Service\AuthenticationService;

class AccessServiceFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     *
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $userConfig = $config['circlical']['user'];
        $guards = $userConfig['guards'];

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