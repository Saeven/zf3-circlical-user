<?php

namespace CirclicalUser\Factory;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AbstractDoctrineMapperFactory implements AbstractFactoryInterface
{
    
    
    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param                         $name
     * @param                         $requestedName
     *
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return strstr($requestedName, '\\Mapper\\');
        
    }
    
    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param                         $name
     * @param                         $requestedName
     *
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $mapper = new $requestedName;
        $mapper->setEntityManager($serviceLocator->get('doctrine.entitymanager.orm_default'));
        
        return $mapper;
    }
}