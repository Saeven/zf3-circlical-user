<?php

namespace Spec\CirclicalUser\Factory\Controller\Plugin;

use CirclicalUser\Controller\Plugin\AuthenticationPlugin;
use CirclicalUser\Service\AccessService;
use CirclicalUser\Service\AuthenticationService;
use PhpSpec\ObjectBehavior;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\ServiceManager\ServiceManager;
use CirclicalUser\Factory\Controller\Plugin\AuthenticationPluginFactory;

class AuthenticationPluginFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AuthenticationPluginFactory::class);
    }

    function it_supports_factory_interface(ServiceManager $serviceLocator, AuthenticationService $authenticationService, AccessService $accessService)
    {
        $serviceLocator->get(AuthenticationService::class)->willReturn($authenticationService);
        $serviceLocator->get(AccessService::class)->willReturn($accessService);

        $this->__invoke($serviceLocator, AuthenticationPlugin::class)->shouldBeAnInstanceOf(AuthenticationPlugin::class);
    }
}
