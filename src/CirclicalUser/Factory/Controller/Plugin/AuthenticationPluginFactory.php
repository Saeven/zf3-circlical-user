<?php

namespace CirclicalUser\Factory\Controller\Plugin;

use CirclicalUser\Service\AccessService;
use CirclicalUser\Service\AuthenticationService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
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
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new AuthenticationPlugin(
            $container->get(AuthenticationService::class),
            $container->get(AccessService::class)
        );
    }
}
