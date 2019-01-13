<?php

namespace Spec\CirclicalUser\Strategy;

use CirclicalUser\Service\AccessService;
use CirclicalUser\Strategy\RedirectStrategy;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Router\RouteMatch;

class RedirectStrategySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RedirectStrategy::class);
    }

    function let()
    {
        $this->beConstructedWith('Controller', 'Action');
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
