<?php

declare(strict_types=1);

namespace CirclicalUser\Strategy;

use CirclicalUser\Provider\DenyStrategyInterface;
use CirclicalUser\Service\AccessService;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;

/**
 * Show the user to a login form if the request is not an XHTTP request, and the gate occurs because no user is
 * logged in.  Do not interject if they are logged in, yet don't have necessary rights.
 */
class RedirectStrategy implements DenyStrategyInterface
{
    /**
     * The controller that dispatch should invoke
     */
    protected string $controllerClass;

    /**
     * This ^ controller's action
     */
    protected string $action;

    private string $routeName;

    public function __construct(string $controllerClass, string $action, string $routeName)
    {
        $this->controllerClass = $controllerClass;
        $this->action = $action;
        $this->routeName = $routeName;
    }

    public function handle(MvcEvent $event, string $eventError): bool
    {
        if ($eventError === AccessService::ACCESS_UNAUTHORIZED && empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $response = $event->getResponse();
            if ($response instanceof Response) {
                $response->setStatusCode(403);
            }

            $routeMatch = new RouteMatch([
                'controller' => $this->controllerClass,
                'action' => $this->action,
            ]);
            $routeMatch->setMatchedRouteName($this->routeName);
            $event->setRouteMatch($routeMatch);
            $event->setParam('authRedirect', true);

            /** @var Request $requestData */
            $requestData = $event->getRequest();

            if ($requestData->getServer('REQUEST_URI')) {
                $event->setParam('authRedirectTo', $requestData->getServer('REQUEST_URI'));
            }

            return true;
        }

        return false;
    }
}
