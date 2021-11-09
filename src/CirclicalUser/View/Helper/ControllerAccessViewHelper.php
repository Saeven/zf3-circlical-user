<?php

declare(strict_types=1);

namespace CirclicalUser\View\Helper;

use CirclicalUser\Service\AccessService;
use Laminas\View\Helper\AbstractHelper;

class ControllerAccessViewHelper extends AbstractHelper
{
    private AccessService $accessService;

    public function __construct(AccessService $accessService)
    {
        $this->accessService = $accessService;
    }

    /**
     * Invoked via 'canAccessController' at the template level
     */
    public function __invoke(string $controllerName): bool
    {
        return $this->accessService->canAccessController($controllerName);
    }
}
