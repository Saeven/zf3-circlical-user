<?php

namespace Spec\CirclicalUser;

use CirclicalUser\Listener\AccessListener;
use PhpSpec\ObjectBehavior;
use Laminas\Console\Console;
use Laminas\EventManager\EventManager;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ModuleSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('CirclicalUser\Module');
    }

    public function it_gets_its_config()
    {
        $this->getConfig()->shouldBeArray();
    }

    public function it_has_a_bootstrap_method(MvcEvent $event, Application $application, ServiceLocatorInterface $serviceLocator,
                                              AccessListener $listener, EventManager $eventManager )
    {
        Console::overrideIsConsole(false);
        $application->getEventManager()->willReturn($eventManager);
        $serviceLocator->get(AccessListener::class)->willReturn($listener);
        $application->getServiceManager()->willReturn($serviceLocator);

        $event->getApplication()->willReturn($application);
        $listener->attach($eventManager)->shouldBeCalled();

        $this->onBootstrap($event);
    }

    public function it_aborts_bootstrap_on_console(MvcEvent $event, Application $application, ServiceLocatorInterface $serviceLocator,
                                              AccessListener $listener, EventManager $eventManager )
    {

        Console::overrideIsConsole(true);
        $application->getEventManager()->willReturn($eventManager);
        $serviceLocator->get(AccessListener::class)->willReturn($listener);
        $application->getServiceManager()->willReturn($serviceLocator);
        $event->getApplication()->willReturn($application);

        $listener->attach($eventManager)->shouldNotBeCalled();

        $this->onBootstrap($event);
    }


}
