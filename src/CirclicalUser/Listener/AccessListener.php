<?php

namespace CirclicalUser\Listener;

use CirclicalUser\Service\AccessService;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
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
            $events->detach($listener);
            unset($this->listeners[$index]);
        }
    }

    public function verifyAccess(MvcEvent $event)
    {
        $route = $event->getRouteMatch();
        $controllerName = $route->getParam('controller');
        $actionName = $route->getParam('action');

        if (!$this->accessService->requiresAuthentication($controllerName, $actionName)) {
            return;
        }

        if ($this->accessService->hasUser()) {
            if ($this->accessService->canAccessAction($controllerName, $actionName)) {
                return;
            }
            $event->setError(AccessService::ACCESS_DENIED);
        } else {
            $event->setError(AccessService::ACCESS_UNAUTHORIZED);
        }

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

    public function onDispatchError(MvcEvent $event)
    {

        switch ($event->getError()) {

            case AccessService::ACCESS_DENIED:
                $statusCode = 403;
                break;
            case AccessService::ACCESS_UNAUTHORIZED:
                $statusCode = 401;
                break;

            default:
                // do nothing if this is a different kind of error we should not trap
                return;
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $viewModel = new JsonModel();
        } else {
            $viewModel = new ViewModel();
            $viewModel->setTemplate('user/' . $statusCode);
        }

        $viewModel->setVariables($event->getParams());
        $response = $event->getResponse() ?: new Response();
        $response->setStatusCode($statusCode);
        $event->setViewModel($viewModel);
        $event->setResponse($response);
    }
}