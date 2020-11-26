<?php

namespace CirclicalUser\Factory\Validator;

use CirclicalUser\Provider\PasswordCheckerInterface;
use CirclicalUser\Validator\PasswordValidator;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class PasswordValidatorFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new PasswordValidator($container->get(PasswordCheckerInterface::class), $options);
    }
}

