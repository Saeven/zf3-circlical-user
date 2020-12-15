<?php

namespace CirclicalUser\Factory\Listener;

use CirclicalUser\Listener\AccessListener;
use CirclicalUser\Service\AccessService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AccessListenerFactory implements FactoryInterface
{

    /**
     * Create an object
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return object
     * @throws \Exception
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