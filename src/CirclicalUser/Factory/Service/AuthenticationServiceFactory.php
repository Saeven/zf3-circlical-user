<?php

namespace CirclicalUser\Factory\Service;

use CirclicalUser\Mapper\UserResetTokenMapper;
use CirclicalUser\Provider\PasswordCheckerInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
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

        $secure = false;
        if (isset($userConfig['auth']['secure_cookies'])) {
            if (is_callable($userConfig['auth']['secure_cookies'] ?? null)) {
                $secure = $userConfig['auth']['secure_cookies']();
                if (!is_bool($secure)) {
                    throw new \RuntimeException("The secure_cookies callback for CirclicalUser must return a boolean value.");
                }
            } else {
                $secure = $userConfig['auth']['secure_cookies'];
            }
        }

        return new AuthenticationService(
            $container->get($authMapper),
            $container->get($userProvider),
            $resetTokenProvider ? $container->get($resetTokenProvider) : null,
            base64_decode($userConfig['auth']['crypto_key']),
            $userConfig['auth']['transient'],
            $secure,
            $container->get(PasswordCheckerInterface::class),
            $userConfig['password_reset_tokens']['validate_fingerprint'] ?? true,
            $userConfig['password_reset_tokens']['validate_ip'] ?? false,
            $userConfig['auth']['samesite_policy'] ?? 'None',
            $userConfig['auth']['cookie_duration'] ?? 2629743
        );
    }
}
