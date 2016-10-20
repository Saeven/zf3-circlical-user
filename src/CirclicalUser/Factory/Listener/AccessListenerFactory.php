<?php

namespace CirclicalUser\Factory\Listener;

use CirclicalUser\Listener\AccessListener;
use CirclicalUser\Service\AccessService;
use CirclicalUser\Strategy\RedirectStrategy;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class AccessListenerFactory implements FactoryInterface
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
        $strategy = null;
        if (!empty($config['circlical']['user']['deny_strategy']['class'])) {
            $strategyClass = $config['circlical']['user']['deny_strategy']['class'];
            if (!class_exists($strategyClass)) {
                throw new \Exception("CirclicalUser > A deny strategy was specified, but the class you specified ('{$strategyClass}') does not exist. Please fix your config.");
            }
            $strategy = $container->get($strategyClass);
        }

        return new AccessListener(
            $container->get(AccessService::class),
            $strategy
        );
    }
}