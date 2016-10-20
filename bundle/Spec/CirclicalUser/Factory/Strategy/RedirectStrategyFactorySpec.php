<?php

namespace Spec\CirclicalUser\Factory\Strategy;

use CirclicalUser\Factory\Strategy\RedirectStrategyFactory;
use CirclicalUser\Strategy\RedirectStrategy;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RedirectStrategyFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RedirectStrategyFactory::class);
    }

    public function it_alerts_that_it_needs_its_configuration(ContainerInterface $container)
    {
        $container->get('config')->willReturn([]);
        $this->shouldThrow(\Exception::class)->during('__invoke', [$container, RedirectStrategy::class]);
    }

    public function it_works_with_a_proper_config(ContainerInterface $container)
    {
        $config = [
            'circlical' => [
                'user' => [
                    'deny_strategy' => [
                        'options' => [
                            'controller' => '\Application\Controller\LoginController',
                            'action' => 'index',
                        ],
                    ],
                ],
            ],
        ];
        $container->get('config')->willReturn($config);
        $this->__invoke($container, RedirectStrategy::class)->shouldHaveType(RedirectStrategy::class);
    }
}
