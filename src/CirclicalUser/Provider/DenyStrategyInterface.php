<?php

declare(strict_types=1);

namespace CirclicalUser\Provider;

use Laminas\Mvc\MvcEvent;

interface DenyStrategyInterface
{
    /**
     * @param string $eventError One of AccessService::ACCESS_DENIED, AccessService::ACCESS_UNAUTHORIZED
     * @return bool True if you want to short-circuit execution. You may for example, want to let it slide for XHTTP requests
     */
    public function handle(MvcEvent $event, string $eventError): bool;
}
