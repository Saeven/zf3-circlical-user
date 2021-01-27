<?php

namespace CirclicalUser\Factory\View\Helper;

use CirclicalUser\Service\AccessService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use CirclicalUser\View\Helper\RoleAccessViewHelper;

class RoleAccessViewHelperFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new RoleAccessViewHelper(
            $container->get(AccessService::class)
        );
    }
}
