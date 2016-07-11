<?php

namespace CirclicalUser\Listener;

use CirclicalUser\Service\AccessService;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class AccessListener implements ListenerAggregateInterface
{
    private $accessService;

    protected $listeners;

    public function __construct(AccessService $accessService)
    {
        $this->listeners = [];
        $this->accessService = $accessService;
    }

    public function attach(EventManagerInterface $events, $priority = 100)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'verifyAccess']);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onDispatchError']);
    }

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    public function verifyAccess(MvcEvent $event)
    {
        $route = $event->getRouteMatch();
        $controllerName = $route->getParam('controller');
        $actionName = $route->getParam('action');

        if ($this->accessService->canAccessAction($controllerName, $actionName)) {
            return;
        }

        $event->setError(AccessService::ACCESS_DENIED);
        $event->setParam('route', $route->getMatchedRouteName());
        $event->setParam('controller', $controllerName);
        $event->setParam('action', $actionName);
        if ($roles = $this->accessService->getRoles()) {
            $event->setParam('roles', implode(',', $roles));
        } else {
            $event->setParam('roles', 'none');
        }

        $app = $event->getTarget();
        $event->setName(MvcEvent::EVENT_DISPATCH_ERROR);
        $app->getEventManager()->triggerEvent($event);
    }

    public function onDispatchError(Event $event)
    {
        switch ($event->getError()) {

            case AccessService::ACCESS_DENIED:
                $viewModel = new ViewModel();
                $viewModel->setVariables($event->getParams());
                $viewModel->setTemplate('user/403');
                $response = $event->getResponse() ?: new Response();
                $response->setStatusCode(403);
                //$event->getViewModel()->addChild($viewModel);
                $event->setViewModel($viewModel);
                $event->setResponse($response);
                break;
            default:
                // do nothing if this is a different kind of error we should not trap
                return;
        }
    }
}