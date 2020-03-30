<?php

namespace CirclicalUser\View\Helper;

use CirclicalUser\Service\AccessService;
use Zend\View\Helper\AbstractHelper;

class RoleAccessViewHelper extends AbstractHelper
{
    private $accessService;

    public function __construct(AccessService $accessService)
    {
        $this->accessService = $accessService;
    }

    /**
     * hasRole
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

