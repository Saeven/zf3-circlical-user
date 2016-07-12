<?php

namespace CirclicalUser\Service;

use CirclicalUser\Entity\Role;
use CirclicalUser\Provider\UserInterface as User;
use CirclicalUser\Exception\GuardConfigurationException;
use CirclicalUser\Exception\UnknownResourceTypeException;
use CirclicalUser\Exception\UserRequiredException;
use CirclicalUser\Provider\ResourceInterface;
use CirclicalUser\Provider\ActionRuleProviderInterface;
use CirclicalUser\Provider\RoleInterface;
use CirclicalUser\Provider\RoleProviderInterface;


class AccessService
{
    const ACCESS_DENIED = 'ACL_ACCESS_DENIED';

    private $user;

    private $controllerDefaults;

    private $actions;

    private $userRoles;

    private $roleProvider;

    private $actionRuleProvider;

    public function __construct(array $guardConfiguration, RoleProviderInterface $roleProvider, ActionRuleProviderInterface $actionRuleProvider)
    {
        $this->roleProvider = $roleProvider;
        $this->actionRuleProvider = $actionRuleProvider;
        $this->controllerDefaults = [];
        $this->actions = [];

        foreach ($guardConfiguration as $module => $config) {
            if (isset($config['controllers'])) {
                foreach ($config['controllers'] as $controllerName => $controllerConfig) {
                    if (isset($controllerConfig['default'])) {
                        if (!is_array($controllerConfig['default'])) {
                            throw new GuardConfigurationException($controllerName, 'the "default" setting must be an array');
                        }
                        $this->controllerDefaults[$controllerName] = $controllerConfig['default'];
                    }

                    if (isset($controllerConfig['actions']) && is_array($controllerConfig['actions'])) {
                        if (!is_array($controllerConfig['actions'])) {
                            throw new GuardConfigurationException($controllerName, 'the "actions" setting must be an array');
                        }

                        foreach ($controllerConfig['actions'] as $action => $actionRoles) {
                            $this->actions[$controllerName][$action] = $actionRoles;
                        }
                    }
                }
            }
        }
    }

    public function setUser(User $user)
    {
        if (!$user->getId()) {
            throw new UserRequiredException();
        }
        $this->user = $user;
    }

    public function canAccessController($controllerName) : bool
    {
        if (!isset($this->controllerDefaults[$controllerName])) {
            return false;
        }
        $requiredRoles = $this->controllerDefaults[$controllerName];
        if (!$requiredRoles) {
            return true;
        }

        if (!$this->user) {
            return false;
        }

        foreach ($requiredRoles as $role) {
            if ($this->hasRoleWithName($role)) {
                return true;
            }
        }

        return false;
    }

    public function canAccessAction($controllerName, $action) : bool
    {
        if (isset($this->actions[$controllerName][$action])) {
            if (!$this->actions[$controllerName][$action]) {
                return true;
            }

            foreach ($this->actions[$controllerName][$action] as $role) {
                if ($this->hasRoleWithName($role)) {
                    return true;
                }
            }

            return false;
        }

        return $this->canAccessController($controllerName);
    }

    public function hasRoleWithName($role) : bool
    {
        $this->compileUserRoles();

        return in_array($role, $this->userRoles);
    }

    public function hasRole(RoleInterface $role) : bool
    {
        return $this->hasRoleWithName($role->getName());
    }

    public function getRoles() : array
    {
        $this->compileUserRoles();

        return $this->userRoles;
    }

    private function compileUserRoles()
    {
        if ($this->userRoles !== null) {
            return;
        }

        if (!$this->user) {
            $this->userRoles = [];

            return;
        }

        $roleList = [];
        $roleExpansion = [];

        /** @var Role $role */
        foreach ($this->roleProvider->getAllRoles() as $role) {
            $roleList[$role->getId()] = $role;
        }

        /** @var Role $userRole */
        foreach ($this->user->getRoles() as $userRole) {
            $roleExpansion[] = $userRole->getName();

            $parentRole = $userRole->getParent();
            while ($parentRole) {
                $roleExpansion[] = $parentRole->getName();
                $parentRole = $parentRole->getParent();
            }
        }
        $this->userRoles = array_unique($roleExpansion);
    }

    public function isAllowed($resource, $action) : bool
    {
        if (is_string($resource)) {
            $actions = $this->actionRuleProvider->getStringActions($resource);
        } elseif ($resource instanceof ResourceInterface) {
            $actions = $this->actionRuleProvider->getResourceActions($resource);
        } else {
            throw new UnknownResourceTypeException();
        }

        // check roles first
        foreach ($actions as $actionRule) {
            if (in_array($action, $actionRule->getActions())) {
                if ($actionRule->getRole() && $this->hasRole($actionRule->getRole())) {
                    return true;
                }
            }
        }

        // check user exceptions second
        foreach ($actions as $actionRule) {
            if (in_array($action, $actionRule->getActions())) {
                if (in_array($this->user, $actionRule->getUserExceptions())) {
                    return true;
                }
            }
        }

        return false;
    }
}