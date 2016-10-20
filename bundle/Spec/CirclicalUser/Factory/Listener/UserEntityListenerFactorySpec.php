<?php

namespace Spec\CirclicalUser\Factory\Listener;

use CirclicalUser\Factory\Listener\UserEntityListenerFactory;
use CirclicalUser\Listener\UserEntityListener;
use Interop\Container\ContainerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserEntityListenerFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UserEntityListenerFactory::class);
    }

    function it_expects_its_configuration(ContainerInterface $container)
    {
        $container->get('config')->willReturn([]);
        $this->shouldThrow(\Exception::class)->during('__invoke', [$container, UserEntityListener::class]);

    }

    function it_returns_a_user_entity_listener_when_properly_configured(ContainerInterface $container)
    {
        $config = [
            'circlical' => [
                'user' => [
                    'doctrine' => [
                        'entity' => 'Application\Entity\User',
                    ],
                ],
            ],
        ];

        $container->get('config')->willReturn($config);
        $this->__invoke($container, UserEntityListener::class)->shouldHaveType(UserEntityListener::class);
    }


}
