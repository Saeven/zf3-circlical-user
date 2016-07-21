<?php

namespace CirclicalUser\Factory\Mapper;

use CirclicalUser\Exception\ConfigurationException;
use CirclicalUser\Mapper\UserMapper;
use CirclicalUser\Provider\UserInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserMapperFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     * @throws ConfigurationException
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $config = $config['circlical']['user'];

        if (!isset($config['doctrine']['entity'])) {
            throw new ConfigurationException("No user provider is defined.  Did you copy the dist config over to your project?");
        }

        $entityClass = $config['doctrine']['entity'];

        if (!class_exists($entityClass)) {
            throw new ConfigurationException("The user entity defined does not exist: $entityClass");
        }

        if (!in_array(UserInterface::class, class_implements($entityClass))) {
            throw new ConfigurationException("The user entity must implement " . UserInterface::class);
        }

        $mapper = new UserMapper($entityClass);
        $mapper->setEntityManager($serviceLocator->get('doctrine.entitymanager.orm_default'));

        return $mapper;
    }
}
