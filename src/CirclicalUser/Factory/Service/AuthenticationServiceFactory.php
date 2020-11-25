<?php

namespace CirclicalUser\Factory\Service;

use CirclicalUser\Exception\PasswordStrengthCheckerException;
use CirclicalUser\Mapper\UserResetTokenMapper;
use CirclicalUser\Provider\PasswordCheckerInterface;
use CirclicalUser\Service\PasswordChecker\PasswordNotChecked;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use CirclicalUser\Service\AuthenticationService;
use CirclicalUser\Mapper\AuthenticationMapper;
use CirclicalUser\Mapper\UserMapper;

class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return AuthenticationService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $userConfig = $config['circlical']['user'];
        $userProvider = $userConfig['providers']['user'] ?? UserMapper::class;
        $authMapper = $userConfig['providers']['auth'] ?? AuthenticationMapper::class;
        $resetTokenProvider = null;

        if (isset($userConfig['auth']['password_reset_tokens']['enabled'])) {
            $resetTokenProvider = $userConfig['providers']['reset'] ?? UserResetTokenMapper::class;
        }

        $passwordChecker = null;
        $passwordCheckerParameters = [];
        if (!empty($userConfig['password_strength_checker'])) {
            if (is_array($userConfig['password_strength_checker'])) {
                if (!is_string($userConfig['password_strength_checker']['implementation'] ?? null) || !is_array($userConfig['password_strength_checker']['config'] ?? null)) {
                    throw new PasswordStrengthCheckerException("When using array notation, the password strength checker must contain 'implementation' and 'config'");
                }
                $checkerImplementation = new $userConfig['password_strength_checker']['implementation'];
                $passwordCheckerParameters = $userConfig['password_strength_checker']['config'];
            } else {
                $checkerImplementation = new $userConfig['password_strength_checker'];
            }

            if ($checkerImplementation instanceof PasswordCheckerInterface) {
                $passwordChecker = $checkerImplementation;
            }

        }

        return new AuthenticationService(
            $container->get($authMapper),
            $container->get($userProvider),
            $resetTokenProvider ? $container->get($resetTokenProvider) : null,
            base64_decode($userConfig['auth']['crypto_key']),
            $userConfig['auth']['transient'],
            false,
            $passwordChecker ?? new PasswordNotChecked(),
            $passwordCheckerParameters,
            $userConfig['password_reset_tokens']['validate_fingerprint'] ?? true,
            $userConfig['password_reset_tokens']['validate_ip'] ?? false
        );
    }
}