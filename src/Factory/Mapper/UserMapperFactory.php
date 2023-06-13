<?php

declare(strict_types=1);

namespace CirclicalUser\Factory\Mapper;

use CirclicalUser\Exception\ConfigurationException;
use CirclicalUser\Mapper\UserMapper;
use CirclicalUser\Provider\UserInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function class_exists;
use function class_implements;
use function in_array;

class UserMapperFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $container->get('config');
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
        $mapper->setEntityManager($container->get('doctrine.entitymanager.orm_default'));

        return $mapper;
    }
}
