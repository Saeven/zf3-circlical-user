<?php

namespace CirclicalUser\Factory\Service;

use CirclicalUser\Provider\PasswordCheckerInterface;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;
use CirclicalUser\Service\AuthenticationService;
use CirclicalUser\Mapper\AuthenticationMapper;
use CirclicalUser\Mapper\UserMapper;

class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     *
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $userConfig = $config['circlical']['user'];

        $userProvider = $userConfig['providers']['user'] ?? UserMapper::class;
        $authMapper = $userConfig['providers']['auth'] ?? AuthenticationMapper::class;
        $passwordChecker = null;

        if( !empty( $userConfig['password_strength_checker'] ) ){
            $checkerImplementation = new $userConfig['password_strength_checker'];
            if( $checkerImplementation instanceof PasswordCheckerInterface ){
                $passwordChecker = $checkerImplementation;
            }
        }


        return new AuthenticationService(
            $container->get($authMapper),
            $container->get($userProvider),
            base64_decode($userConfig['auth']['crypto_key']),
            $userConfig['auth']['transient'],
            false,
            $passwordChecker
        );
    }
}