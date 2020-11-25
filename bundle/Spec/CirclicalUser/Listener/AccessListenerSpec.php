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
    private const CONTROLLER_INDEX = '\Namespace\Controller\IndexController';
    private const CONTROLLER_ADMIN = '\Namespace\Controller\AdminController';

    private const CONTROLLER_MIDDLEWARE_1 = '\Namespace\Controller\MiddlewareInterface1';
    private const CONTROLLER_MIDDLEWARE_2 = '\Namespace\Controller\MiddlewareInterface2';

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

    public function it_can_permit_access_based_on_controller(MvcEvent $event, RouteMatch $match, $accessService)
    {
        $accessService->requiresAuthentication(self::CONTROLLER_INDEX, 'index')->willReturn(true);
        $accessService->hasUser()->willReturn(true);
        $match->getParam('controller')->willReturn(self::CONTROLLER_INDEX);
        $match->getParam('action')->willReturn('index');
        $match->getParam('middleware')->willReturn(null);
        $event->getRouteMatch()->willReturn($match);
        $this->verifyAccess($event);
    }

    public function it_can_deny_access_based_on_controller(MvcEvent $event, RouteMatch $match, EventManagerInterface $eventManager, Application $application, $accessService)
    {

        $accessService->requiresAuthentication(self::CONTROLLER_ADMIN, 'index')->willReturn(true);
        $accessService->hasUser()->willReturn(true);

        $application->getEventManager()->willReturn($eventManager);

        $event->getTarget()->willReturn($application);
        $match->getParam('controller')->willReturn(self::CONTROLLER_ADMIN);
        $match->getParam('action')->willReturn('index');
        $match->getParam('middleware')->willReturn(null);
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
        $accessService->requiresAuthentication(self::CONTROLLER_ADMIN, 'index')->willReturn(true);
        $accessService->hasUser()->willReturn(true);

        $application->getEventManager()->willReturn($eventManager);

        $event->getTarget()->willReturn($application);
        $match->getParam('controller')->willReturn(self::CONTROLLER_ADMIN);
        $match->getParam('action')->willReturn('index');
        $match->getParam('middleware')->willReturn(null);
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


    public function it_dispatches_errors_properly(MvcEvent $event, Response $mvcResponse)
    {
        $event->getParams()->willReturn(['a' => 1]);
        $event->getResponse()->willReturn($mvcResponse);
        $event->getError()->willReturn(AccessService::ACCESS_DENIED);

        $event->setResponse(Argument::type(Response::class))->shouldBeCalled();
        $event->setViewModel(Argument::type(ViewModel::class))->shouldBeCalled();


        $this->onDispatchError($event);

    }

    public function it_dispatches_ajax_errors_properly(MvcEvent $event, Response $mvcResponse)
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';

        $event->getParams()->willReturn(['a' => 1]);
        $event->getResponse()->willReturn($mvcResponse);
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

    /**
     * Valid route but with no login with no login
     */
    public function it_dispatches_unauthorized(MvcEvent $event, RouteMatch $match, EventManagerInterface $eventManager, Application $application, $accessService)
    {
        $application->getEventManager()->willReturn($eventManager);
        $accessService->requiresAuthentication(self::CONTROLLER_ADMIN, 'index')->willReturn(true);
        $accessService->hasUser()->willReturn(false);

        $event->getTarget()->willReturn($application);
        $match->getParam('controller')->willReturn(self::CONTROLLER_ADMIN);
        $match->getParam('action')->willReturn('index');
        $match->getParam('middleware')->willReturn(null);
        $match->getMatchedRouteName()->willReturn('admin');
        $event->getRouteMatch()->willReturn($match);
        $accessService->getRoles()->willReturn([]);

        $eventManager->triggerEvent($event)->shouldBeCalled();
        $event->setError(AccessService::ACCESS_UNAUTHORIZED)->shouldBeCalled();
        $event->setParam('route', 'admin')->shouldBeCalled();
        $event->setParam('controller', self::CONTROLLER_ADMIN)->shouldBeCalled();
        $event->setParam('action', 'index')->shouldBeCalled();
        $event->setParam('roles', 'none')->shouldBeCalled();
        $event->setName(MvcEvent::EVENT_DISPATCH_ERROR)->shouldBeCalled();


        $this->verifyAccess($event);
    }

    /**
     * zend-mvc recently added middleware support to routing; controllers and actions are not passed in, so we
     * have to examine the middleware param on the routematch to see if we should grant access
     *
     * @param \PhpSpec\Wrapper\Collaborator|MvcEvent              $event
     * @param \PhpSpec\Wrapper\Collaborator|RouteMatch            $match
     * @param \PhpSpec\Wrapper\Collaborator|EventManagerInterface $eventManager
     * @param \PhpSpec\Wrapper\Collaborator|Application           $application
     * @param \PhpSpec\Wrapper\Collaborator                       $accessService
     */
    public function it_denies_unauthorized_mvc_middleware_string_requests(MvcEvent $event, RouteMatch $match, EventManagerInterface $eventManager, Application $application, $accessService)
    {
        $application->getEventManager()->willReturn($eventManager);
        $accessService->canAccessController(self::CONTROLLER_MIDDLEWARE_1)->willReturn(false);
        $accessService->hasUser()->willReturn(false);

        $event->getTarget()->willReturn($application);
        $match->getParam('controller')->willReturn(null);
        $match->getParam('action')->willReturn(null);
        $match->getParam('middleware')->willReturn(self::CONTROLLER_MIDDLEWARE_1);
        $match->getMatchedRouteName()->willReturn('middleware-test');
        $event->getRouteMatch()->willReturn($match);
        $accessService->getRoles()->willReturn([]);

        $eventManager->triggerEvent($event)->shouldBeCalled();
        $event->setError(AccessService::ACCESS_UNAUTHORIZED)->shouldBeCalled();
        $event->setParam('route', 'middleware-test')->shouldBeCalled();
        $event->setParam('controller', self::CONTROLLER_MIDDLEWARE_1)->shouldBeCalled();
        $event->setParam('action', 'none')->shouldBeCalled();
        $event->setParam('roles', 'none')->shouldBeCalled();
        $event->setName(MvcEvent::EVENT_DISPATCH_ERROR)->shouldBeCalled();

        $this->verifyAccess($event);
    }

    /**
     * As of zend-mvc 3.1.0, middleware definitions can also be defined as arrays.  Similar to the previous test, but with
     * an array structure as middleware route definition.
     *
     * @param \PhpSpec\Wrapper\Collaborator|MvcEvent              $event
     * @param \PhpSpec\Wrapper\Collaborator|RouteMatch            $match
     * @param \PhpSpec\Wrapper\Collaborator|EventManagerInterface $eventManager
     * @param \PhpSpec\Wrapper\Collaborator|Application           $application
     * @param \PhpSpec\Wrapper\Collaborator                       $accessService
     */
    public function it_denies_unauthorized_mvc_middleware_array_requests(MvcEvent $event, RouteMatch $match, EventManagerInterface $eventManager, Application $application, $accessService)
    {
        $application->getEventManager()->willReturn($eventManager);
        $accessService->canAccessController(self::CONTROLLER_MIDDLEWARE_1)->willReturn(true);
        $accessService->canAccessController(self::CONTROLLER_MIDDLEWARE_2)->willReturn(false);
        $accessService->hasUser()->willReturn(false);

        $event->getTarget()->willReturn($application);
        $match->getParam('controller')->willReturn(null);
        $match->getParam('action')->willReturn(null);
        $match->getParam('middleware')->willReturn([self::CONTROLLER_MIDDLEWARE_1, self::CONTROLLER_MIDDLEWARE_2]);
        $match->getMatchedRouteName()->willReturn('middleware-test');
        $event->getRouteMatch()->willReturn($match);
        $accessService->getRoles()->willReturn([]);

        $eventManager->triggerEvent($event)->shouldBeCalled();
        $event->setError(AccessService::ACCESS_UNAUTHORIZED)->shouldBeCalled();
        $event->setParam('route', 'middleware-test')->shouldBeCalled();
        $event->setParam('controller', self::CONTROLLER_MIDDLEWARE_2)->shouldBeCalled();
        $event->setParam('action', 'none')->shouldBeCalled();
        $event->setParam('roles', 'none')->shouldBeCalled();
        $event->setName(MvcEvent::EVENT_DISPATCH_ERROR)->shouldBeCalled();

        $this->verifyAccess($event);
    }
}
