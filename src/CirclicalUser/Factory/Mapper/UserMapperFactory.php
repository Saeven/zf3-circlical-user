<?php

namespace CirclicalUser\Factory\Mapper;

use CirclicalUser\Exception\ConfigurationException;
use CirclicalUser\Mapper\UserMapper;
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
        $config = $config['circlical']['user']['providers'];
        if (!isset($config['user'])) {
            throw new ConfigurationException("No user provider is defined.  Did you copy the dist config over to your project?");
        }

        if (!class_exists($config['user'])) {
            throw new ConfigurationException("The user entity defined does not exist: " . $config['user']);
        }

        return new UserMapper($config['user']);
    }
}
