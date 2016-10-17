<?php

namespace Spec\CirclicalUser\Listener;

use CirclicalUser\Listener\AccessListener;
use CirclicalUser\Service\AccessService;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Response;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Router\RouteMatch;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class AccessListenerSpec extends ObjectBehavior
{
    const CONTROLLER_INDEX = '\Namespace\Controller\IndexController';
    const CONTROLLER_ADMIN = '\Namespace\Controller\AdminController';

    function it_is_initializable()
    {
        $this->shouldHaveType(AccessListener::class);
    }

    function let(AccessService $accessService)
    {
        $accessService->canAccessAction(self::CONTROLLER_INDEX, 'index')->willReturn(true);
        $accessService->canAccessAction(self::CONTROLLER_ADMIN, 'index')->willReturn(false);

        $this->beConstructedWith($accessService);
    }

    public function it_can_attach_to_an_event_manager(EventManagerInterface $events)
    {
        $this->attach($events);
    }

    public function it_can_detach_from_an_event_manager(EventManagerInterface $events)
    {
        $callable = function () {
        };

        $events->attach(Argument::any(), Argument::any())->willReturn($callable);
        $this->attach($events);

        $events->detach(Argument::any())->willReturn(null);
        $this->detach($events);
    }

    public function it_can_permit_access_based_on_controller(MvcEvent $event, RouteMatch $match)
    {
        $match->getParam('controller')->willReturn(self::CONTROLLER_INDEX);
        $match->getParam('action')->willReturn('index');
        $event->getRouteMatch()->willReturn($match);
        $this->verifyAccess($event);
    }

    public function it_can_deny_access_based_on_controller(MvcEvent $event, RouteMatch $match, EventManagerInterface $eventManager, Application $application, $accessService)
    {

        $application->getEventManager()->willReturn($eventManager);

        $event->getTarget()->willReturn($application);
        $match->getParam('controller')->willReturn(self::CONTROLLER_ADMIN);
        $match->getParam('action')->willReturn('index');
        $match->getMatchedRouteName()->willReturn('admin');
        $event->getRouteMatch()->willReturn($match);
        $accessService->getRoles()->willReturn([]);

        $eventManager->triggerEvent($event)->shouldBeCalled();
        $event->setError(AccessService::ACCESS_DENIED)->shouldBeCalled();
        $event->setParam('route', 'admin')->shouldBeCalled();
        $event->setParam('controller', self::CONTROLLER_ADMIN)->shouldBeCalled();
        $event->setParam('action', 'index')->shouldBeCalled();
        $event->setParam('roles', 'none')->shouldBeCalled();
        $event->setName(MvcEvent::EVENT_DISPATCH_ERROR)->shouldBeCalled();


        $this->verifyAccess($event);
    }

    public function it_can_deny_access_based_on_controller_and_set_roles(MvcEvent $event, RouteMatch $match, EventManagerInterface $eventManager, Application $application, $accessService)
    {

        $application->getEventManager()->willReturn($eventManager);

        $event->getTarget()->willReturn($application);
        $match->getParam('controller')->willReturn(self::CONTROLLER_ADMIN);
        $match->getParam('action')->willReturn('index');
        $match->getMatchedRouteName()->willReturn('admin');
        $event->getRouteMatch()->willReturn($match);
        $accessService->getRoles()->willReturn(['user']);

        $eventManager->triggerEvent($event)->shouldBeCalled();
        $event->setError(AccessService::ACCESS_DENIED)->shouldBeCalled();
        $event->setParam('route', 'admin')->shouldBeCalled();
        $event->setParam('controller', self::CONTROLLER_ADMIN)->shouldBeCalled();
        $event->setParam('action', 'index')->shouldBeCalled();
        $event->setParam('roles', 'user')->shouldBeCalled();
        $event->setName(MvcEvent::EVENT_DISPATCH_ERROR)->shouldBeCalled();


        $this->verifyAccess($event);
    }


    public function it_dispatches_errors_properly(MvcEvent $event)
    {
        $event->getParams()->willReturn(['a' => 1]);
        $event->getResponse()->willReturn(null);
        $event->getError()->willReturn(AccessService::ACCESS_DENIED);

        $event->setResponse(Argument::type(Response::class))->shouldBeCalled();
        $event->setViewModel(Argument::type(ViewModel::class))->shouldBeCalled();


        $this->onDispatchError($event);

    }

    public function it_dispatches_ajax_errors_properly(MvcEvent $event)
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';

        $event->getParams()->willReturn(['a' => 1]);
        $event->getResponse()->willReturn(null);
        $event->getError()->willReturn(AccessService::ACCESS_DENIED);

        $event->setResponse(Argument::type(Response::class))->shouldBeCalled();
        $event->setViewModel(Argument::type(JsonModel::class))->shouldBeCalled();


        $this->onDispatchError($event);
    }

    public function it_ignores_errors_it_does_not_handle(MvcEvent $event)
    {
        $event->getError()->willReturn(MvcEvent::EVENT_RENDER_ERROR);
        $event->setResponse(Argument::any())->shouldNotBeCalled();
        $this->onDispatchError($event);
    }
}
