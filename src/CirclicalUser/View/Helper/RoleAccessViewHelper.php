<?php

namespace CirclicalUser\View\Helper;

use CirclicalUser\Service\AccessService;
use Laminas\View\Helper\AbstractHelper;

class RoleAccessViewHelper extends AbstractHelper
{
    private AccessService $accessService;

    public function __construct(AccessService $accessService)
    {
        $this->accessService = $accessService;
    }

    /**
     * Invoked via 'hasRole' from a template
     */
    public function __invoke($roleNameOrList): bool
    {
        $roles = $roleNameOrList;
        if (is_string($roleNameOrList)) {
            $roles = [$roleNameOrList];
        }

        foreach ($roles as $roleName) {
            if ($this->accessService->hasRoleWithName($roleName)) {
                return true;
            }
        }

        return false;
    }
}
