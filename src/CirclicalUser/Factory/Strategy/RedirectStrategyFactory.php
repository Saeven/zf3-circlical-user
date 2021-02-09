<?php

namespace CirclicalUser\Factory\Strategy;

use CirclicalUser\Strategy\RedirectStrategy;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class RedirectStrategyFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        if (!isset($config['circlical']['user']['deny_strategy']['options'])) {
            throw new \InvalidArgumentException("CirclicalUser > Please check your config. You specified the module-provided redirect strategy, but didn't include the provided configuration.");
        }
        $appliedOptions = $config['circlical']['user']['deny_strategy']['options'];

        return new RedirectStrategy($appliedOptions['controller'], $appliedOptions['action']);
    }
}
