<?php

namespace CirclicalUser\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
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
        $mapper = new $requestedName;
        $mapper->setEntityManager($container->get('doctrine.entitymanager.orm_default'));

        return $mapper;
    }
}