<?php

declare(strict_types=1);

namespace CirclicalUser\Factory;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;

use function str_contains;

class AbstractDoctrineMapperFactory implements AbstractFactoryInterface
{
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return str_contains($requestedName, '\\Mapper\\');
    }

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        /** @psalm-suppress InvalidStringClass */
        $mapper = new $requestedName();
        $mapper->setEntityManager($container->get('doctrine.entitymanager.orm_default'));

        return $mapper;
    }
}
