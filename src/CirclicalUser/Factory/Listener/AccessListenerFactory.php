<?php

namespace CirclicalUser\Factory\Listener;

use CirclicalUser\Listener\AccessListener;
use CirclicalUser\Service\AccessService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AccessListenerFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new AccessListener(
            $serviceLocator->get(AccessService::class)
        );
    }
}