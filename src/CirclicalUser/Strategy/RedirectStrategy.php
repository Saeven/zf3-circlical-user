<?php

namespace CirclicalUser\Strategy;

use CirclicalUser\Provider\DenyStrategyInterface;
use CirclicalUser\Service\AccessService;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;

/**
 * Class RedirectStrategy
 * @package CirclicalUser\Strategy
 *
 * Show the user to a login form if the request is not an XHTTP request, and the gate occurs because no user is
 * logged in.  Do not interject if they are logged in, yet don't have necessary rights.
 */
class RedirectStrategy implements DenyStrategyInterface
{
    /**
     * The controller that dispatch should invoke
     * @var string
     */
    protected $controllerClass;

    /**
     * This ^ controller's action
     * @var string
     */
    protected $action;


    public function __construct(string $controllerClass, string $action)
    {
        $this->controllerClass = $controllerClass;
        $this->action = $action;
    }

    public function handle(MvcEvent $event, string $eventError): bool
    {
        if ($eventError === AccessService::ACCESS_UNAUTHORIZED && empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {

            $response = $event->getResponse();
            if ($response instanceof Response) {
                $response->setStatusCode(403);
            }
            $event->setRouteMatch(new RouteMatch([
                'controller' => $this->controllerClass,
                'action' => $this->action,
            ]));
            $event->setParam('authRedirect', true);

            /** @var \Laminas\Http\PhpEnvironment\Request $requestData */
            $requestData = $event->getRequest();

            if ($requestData->getServer('REQUEST_URI')) {
                $event->setParam('authRedirectTo', $requestData->getServer('REQUEST_URI'));
            }

            return true;
        }

        return false;
    }
}