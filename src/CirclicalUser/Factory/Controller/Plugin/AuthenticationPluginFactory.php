<?php

namespace CirclicalUser\Factory\Controller\Plugin;

use CirclicalUser\Service\AccessService;
use CirclicalUser\Service\AuthenticationService;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;
use CirclicalUser\Controller\Plugin\AuthenticationPlugin;


class AuthenticationPluginFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new AuthenticationPlugin(
            $container->get(AuthenticationService::class),
            $container->get(AccessService::class)
        );
    }
}