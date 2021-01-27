<?php

namespace CirclicalUser\View\Helper;

use CirclicalUser\Service\AccessService;
use Zend\View\Helper\AbstractHelper;

class ControllerAccessViewHelper extends AbstractHelper
{
    private $accessService;

    public function __construct(AccessService $accessService)
    {
        $this->accessService = $accessService;
    }

    /**
     * canAccessController
     */
    public function __invoke(string $controllerName): bool
    {
        return $this->accessService->canAccessController($controllerName);
    }
}
