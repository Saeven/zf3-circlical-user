<?php

declare(strict_types=1);

namespace CirclicalUser\Factory\Service\PasswordChecker;

use CirclicalUser\Exception\PasswordStrengthCheckerException;
use CirclicalUser\Provider\PasswordCheckerInterface;
use CirclicalUser\Service\PasswordChecker\PasswordNotChecked;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

use function is_array;
use function is_string;

class PasswordCheckerFactory implements FactoryInterface
{
    /**
     * @throws PasswordStrengthCheckerException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $container->get('config');
        $userConfig = $config['circlical']['user'];

        if (!empty($userConfig['password_strength_checker'])) {
            if (is_array($userConfig['password_strength_checker'])) {
                if (!is_string($userConfig['password_strength_checker']['implementation'] ?? null) || !is_array($userConfig['password_strength_checker']['config'] ?? null)) {
                    throw new PasswordStrengthCheckerException("When using array notation, the password strength checker must contain 'implementation' and 'config'");
                }
                $checkerImplementation = new $userConfig['password_strength_checker']['implementation']($userConfig['password_strength_checker']['config']);
            } else {
                $checkerImplementation = new $userConfig['password_strength_checker']();
            }

            if (!$checkerImplementation instanceof PasswordCheckerInterface) {
                throw new RuntimeException("An invalid type of password checker was specified!");
            }

            return $checkerImplementation;
        }

        return new PasswordNotChecked();
    }
}
