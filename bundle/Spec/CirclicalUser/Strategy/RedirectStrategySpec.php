<?php

namespace Spec\CirclicalUser\Strategy;

use CirclicalUser\Service\AccessService;
use CirclicalUser\Strategy\RedirectStrategy;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;

class RedirectStrategySpec extends ObjectBehavior
{
    static $requestUri = '/member';

    function it_is_initializable()
    {
        $this->shouldHaveType(RedirectStrategy::class);
    }

    function let(Request $request)
    {
        $this->beConstructedWith('Controller', 'Action');
        $request->getServer('REQUEST_URI')->willReturn(static::$requestUri);
    }

    function it_traps_unauthorized(MvcEvent $event, Response $response, Application $application, Request $request)
    {
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        $event->getTarget()->willReturn($application);
        $event->getRequest()->willReturn($request);
        $event->setRouteMatch(new RouteMatch([
            'controller' => 'Controller',
            'action' => 'Action',
        ]))->shouldBeCalled();
        $event->setParam('authRedirect', true)->shouldBeCalled();
        $event->setParam('authRedirectTo', static::$requestUri)->shouldBeCalled();
        $response->setStatusCode(403)->shouldBeCalled();
        $event->getResponse()->willReturn($response);
        $this->handle($event, AccessService::ACCESS_UNAUTHORIZED)->shouldBe(true);
    }

    function it_ignores_unauthorized_via_xhttp(MvcEvent $event)
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
        $this->handle($event, AccessService::ACCESS_UNAUTHORIZED)->shouldBe(false);
    }
}
