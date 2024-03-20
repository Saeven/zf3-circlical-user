<?php

declare(strict_types=1);

namespace CirclicalUser\View\Helper;

use CirclicalUser\Service\AccessService;
use Laminas\View\Helper\AbstractHelper;

use function is_array;

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
    public function __invoke(string|array $roleNameOrList): bool
    {
        if (!is_array($roleNameOrList)) {
            $roleNameOrList = [$roleNameOrList];
        }

        foreach ($roleNameOrList as $roleName) {
            if ($this->accessService->hasRoleWithName($roleName)) {
                return true;
            }
        }

        return false;
    }
}
