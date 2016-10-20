<?php

namespace Spec\CirclicalUser\Factory\Listener;

use CirclicalUser\Listener\AccessListener;
use CirclicalUser\Service\AccessService;
use CirclicalUser\Strategy\RedirectStrategy;
use PhpSpec\Exception\Exception;
use PhpSpec\ObjectBehavior;
use Zend\ServiceManager\ServiceManager;

class AccessListenerFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('CirclicalUser\Factory\Listener\AccessListenerFactory');
    }

    function it_supports_factory_interface(ServiceManager $serviceLocator, AccessService $accessService)
    {
        $serviceLocator->get(AccessService::class)->willReturn($accessService);
        $serviceLocator->get('config')->willReturn([]);
        $this->__invoke($serviceLocator, AccessListener::class)->shouldBeAnInstanceOf(AccessListener::class);
    }


    function it_supports_factory_interface_with_strategy(ServiceManager $serviceLocator, AccessService $accessService, RedirectStrategy $redirectStrategy)
    {
        $config = [
            'circlical' => [
                'user' => [
                    'deny_strategy' => [
                        'class' => RedirectStrategy::class,
                        'options' => [
                            'controller' => '\Application\Controller\LoginController',
                            'action' => 'index',
                        ],
                    ],
                ],
            ],
        ];

        $serviceLocator->get(RedirectStrategy::class)->willReturn($redirectStrategy);
        $serviceLocator->get(AccessService::class)->willReturn($accessService);
        $serviceLocator->get('config')->willReturn($config);
        $this->__invoke($serviceLocator, AccessListener::class)->shouldBeAnInstanceOf(AccessListener::class);
    }


    function it_throws_exceptions_for_absent_strategy_specifications(ServiceManager $serviceLocator, AccessService $accessService, RedirectStrategy $redirectStrategy)
    {
        $config = [
            'circlical' => [
                'user' => [
                    'deny_strategy' => [
                        'class' => 'IDontExistStrategy',
                        'options' => [
                            'controller' => '\Application\Controller\LoginController',
                            'action' => 'index',
                        ],
                    ],
                ],
            ],
        ];

        $serviceLocator->get('IDontExistStrategy')->shouldNotBeCalled();
        $serviceLocator->get(AccessService::class)->willReturn($accessService);
        $serviceLocator->get('config')->willReturn($config);

        $this->shouldThrow(\Exception::class)->during('__invoke', [$serviceLocator, AccessListener::class]);
    }

}
