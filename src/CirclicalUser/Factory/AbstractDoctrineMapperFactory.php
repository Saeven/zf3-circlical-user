<?php

declare(strict_types=1);

namespace CirclicalUser\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

use function strstr;

class AbstractDoctrineMapperFactory implements AbstractFactoryInterface
{
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return strstr($requestedName, '\\Mapper\\') !== false;
    }

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $mapper = new $requestedName();
        $mapper->setEntityManager($container->get('doctrine.entitymanager.orm_default'));

        return $mapper;
    }
}
