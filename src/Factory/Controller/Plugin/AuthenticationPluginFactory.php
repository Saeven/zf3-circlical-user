<?php

declare(strict_types=1);

namespace CirclicalUser\Factory\Controller\Plugin;

use CirclicalUser\Controller\Plugin\AuthenticationPlugin;
use CirclicalUser\Service\AccessService;
use CirclicalUser\Service\AuthenticationService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class AuthenticationPluginFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new AuthenticationPlugin(
            $container->get(AuthenticationService::class),
            $container->get(AccessService::class)
        );
    }
}
