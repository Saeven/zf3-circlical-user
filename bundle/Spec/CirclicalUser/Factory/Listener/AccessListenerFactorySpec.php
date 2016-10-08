<?php

namespace Spec\CirclicalUser\Factory\Listener;

use CirclicalUser\Listener\AccessListener;
use CirclicalUser\Service\AccessService;
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
        $this->__invoke($serviceLocator, AccessListener::class)->shouldBeAnInstanceOf(AccessListener::class);
    }
}
