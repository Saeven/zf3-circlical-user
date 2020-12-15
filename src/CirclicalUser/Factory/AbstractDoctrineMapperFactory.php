<?php

namespace CirclicalUser\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class AbstractDoctrineMapperFactory implements AbstractFactoryInterface
{


    /**
     * Determine if we can create a service with name
     *
     * @param ContainerInterface|ServiceLocatorInterface $serviceLocator
     * @param string                                     $requestedName
     *
     * @return bool
     * @internal param $name
     */
    public function canCreate(ContainerInterface $serviceLocator, $requestedName)
    {
        return strstr($requestedName, '\\Mapper\\') != null;
    }


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
        $mapper = new $requestedName;
        $mapper->setEntityManager($container->get('doctrine.entitymanager.orm_default'));

        return $mapper;
    }
}