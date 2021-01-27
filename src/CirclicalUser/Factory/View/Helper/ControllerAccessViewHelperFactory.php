<?php

namespace CirclicalUser\Factory\View\Helper;

use CirclicalUser\Service\AccessService;
use CirclicalUser\View\Helper\ControllerAccessViewHelper;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ControllerAccessViewHelperFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ControllerAccessViewHelper(
            $container->get(AccessService::class)
        );
    }
}
