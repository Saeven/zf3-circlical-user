<?php

namespace CirclicalUser\Provider;

use Zend\Mvc\MvcEvent;

interface DenyStrategyInterface
{
    /**
     * @param MvcEvent $event
     * @param string   $eventError One of AccessService::ACCESS_DENIED, AccessService::ACCESS_UNAUTHORIZED
     *
     * @return bool True if you want to short-circuit execution. You may for example, want to let it slide for XHTTP requests
     */
    public function handle(MvcEvent $event, string $eventError): bool;
}
