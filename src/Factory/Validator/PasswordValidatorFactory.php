<?php

declare(strict_types=1);

namespace CirclicalUser\Factory\Validator;

use CirclicalUser\Provider\PasswordCheckerInterface;
use CirclicalUser\Validator\PasswordValidator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class PasswordValidatorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new PasswordValidator($container->get(PasswordCheckerInterface::class), $options);
    }
}
