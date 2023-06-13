<?php

declare(strict_types=1);

namespace CirclicalUser\Factory\View\Helper;

use CirclicalUser\Service\AccessService;
use CirclicalUser\View\Helper\RoleAccessViewHelper;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class RoleAccessViewHelperFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new RoleAccessViewHelper(
            $container->get(AccessService::class)
        );
    }
}
