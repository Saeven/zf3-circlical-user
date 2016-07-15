<?php

namespace CirclicalUser\Service;

use CirclicalUser\Entity\Role;
use CirclicalUser\Exception\RuleExpectedException;
use CirclicalUser\Provider\GroupActionRuleProviderInterface;
use CirclicalUser\Provider\UserActionRuleInterface;
use CirclicalUser\Provider\UserInterface as User;
use CirclicalUser\Exception\GuardConfigurationException;
use CirclicalUser\Exception\UnknownResourceTypeException;
use CirclicalUser\Exception\UserRequiredException;
use CirclicalUser\Provider\ResourceInterface;
use CirclicalUser\Provider\UserActionRuleProviderInterface;
use CirclicalUser\Provider\RoleInterface;
use CirclicalUser\Provider\RoleProviderInterface;
use phpDocumentor\Reflection\Types\Resource;


class AccessService
{
    const ACCESS_DENIED = 'ACL_ACCESS_DENIED';

    private $user;

    private $controllerDefaults;

    private $actions;

    private $userRoles;

    private $roleProvider;

    private $groupRules;

    private $userRules;


    public function __construct(array $guardConfiguration, RoleProviderInterface $roleProvider,
                                GroupActionRuleProviderInterface $groupRules, UserActionRuleProviderInterface $userRules)
    {
        $this->roleProvider = $roleProvider;
        $this->groupRules = $groupRules;
        $this->userRules = $userRules;
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

                    if (isset($controllerConfig['actions'])) {

                        if (!is_array($controllerConfig['actions'])) {
                            throw new GuardConfigurationException($controllerName, 'the "actions" setting must be an array');
                        }

                        foreach ($controllerConfig['actions'] as $action => $actionRoles) {
                            if (!is_array($controllerConfig['actions'][$action])) {
                                throw new GuardConfigurationException($controllerName, 'the roles for action "$action" must be an array');
                            }
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

    /**
     * Check the guard configuration to see if the current user (or guest) can access a specific controller.
     * Critical distinction: this method does not invoke action rules, only roles.
     *
     * @param $controllerName
     *
     * @return bool
     */
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

    /**
     * Similar to controller access, see if the config array grants the current user (or guest) access to a specific
     * action on a given controller.
     *
     * @param $controllerName
     * @param $action
     *
     * @return bool
     */
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

    /**
     * Check if the current user has a given role.
     *
     * @param $role
     *
     * @return bool True if the role fits, false if there is no user or the role is not accessible in the hierarchy
     *              of existing user roles.
     */
    public function hasRoleWithName($role) : bool
    {
        $this->compileUserRoles();

        return in_array($role, $this->userRoles);
    }

    /**
     * Convenience method that defers to the 'withName' method.
     *
     * @see self::hasRoleWithName
     *
     * @param RoleInterface $role
     *
     * @return bool
     */
    public function hasRole(RoleInterface $role) : bool
    {
        return $this->hasRoleWithName($role->getName());
    }

    /**
     * Get the string IDs of all roles accessible to the current user.  If your user has 'admin' role, and 'admin'
     * is a super-role to 'user', this method will return ['admin','user'].
     *
     * @return array
     */
    public function getRoles() : array
    {
        $this->compileUserRoles();

        return $this->userRoles;
    }

    /**
     * Flattens roles using the roleProvider, for quick lookup.
     */
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

    /**
     * Actions are an ability to do 'something' with either a 'string' or ResourceInterface as the subject.  Some
     * actions are attributed to roles, as defined by your roleProvider.  This method checks to see if the set of
     * roles associated to your user, grants access to a specific verb on a resource.
     *
     * @param $resource
     *
     * @return array
     * @throws UnknownResourceTypeException
     */
    public function getGroupActions($resource) : array
    {
        if ($resource instanceof ResourceInterface) {
            return $this->groupRules->getResourceActions($resource);
        }

        if (is_string($resource)) {
            return $this->groupRules->getStringActions($resource);
        }

        throw new UnknownResourceTypeException(get_class($resource));
    }

    /**
     * Action rules can also be defined at a user level.  Similar to group rules (e.g., all admins can 'shutdown' 'servers'),
     * you can give users individual privileges on verbs and resources. You can create circumstances such as
     * "all admins can 'shutdown' 'servers', and user 45 can do it too!"
     *
     * This method expects that a user has been set by the Factory
     *
     * @param $resource
     *
     * @return UserActionRuleInterface
     * @throws UnknownResourceTypeException
     * @throws UserRequiredException
     */
    public function getUserActions($resource)
    {
        if (!$this->user) {
            throw new UserRequiredException();
        }

        if ($resource instanceof ResourceInterface) {
            return $this->userRules->getUserResourceActions($resource, $this->user);
        }

        if (is_string($resource)) {
            return $this->userRules->getUserStringActions($resource, $this->user);
        }

        throw new UnknownResourceTypeException(get_class($resource));
    }

    /**
     * This is the crux of all resource and verb checks.  Two important concepts:
     *
     * - Resources can be simple strings, or can be objects that implement ResourceInterface
     * - Actions are an action string
     *
     * It was a design condition to favor consistent method invocation, and let this library handle string or
     * resource distinction, rather than force you to differentiate the cases in your code.
     *
     * @param $resource
     * @param $action
     *
     * @return bool
     */
    public function isAllowed($resource, $action) : bool
    {
        $actions = $this->getGroupActions($resource);

        // check roles first
        foreach ($actions as $actionRule) {
            if (in_array($action, $actionRule->getActions())) {
                if ($actionRule->getRole() && $this->hasRole($actionRule->getRole())) {
                    return true;
                }
            }
        }

        if ($this->user) {
            return $this->isAllowedUser($resource, $action);
        }

        return false;
    }

    /**
     * Similar to isAllowed, this method checks user-rules specifically.  If there is no user in session, and this
     * method is called directly, a UserRequiredException will be thrown.
     *
     * isAllowed, will pass the buck to this method if no group rules satisfy the action.
     *
     * @param $resource
     * @param $action
     *
     * @return bool
     */
    public function isAllowedUser($resource, $action) : bool
    {
        $actionRule = $this->getUserActions($resource);

        if ($actionRule) {
            if (in_array($action, $actionRule->getActions())) {
                return true;
            }
        }

        return false;
    }

    public function grantUserAccess($resource, $action)
    {
        $resourceRule = $this->getUserActions($resource);

        // make sure we can work with this
        if ($resourceRule) {
            if (!($resourceRule instanceof UserActionRuleInterface)) {
                throw new RuleExpectedException(UserActionRuleInterface::class, get_class($resourceRule));
            }
        }

        /** @var UserActionRuleInterface $resourceRule */
        if ($resourceRule) {
            if (in_array($action, $resourceRule->getActions())) {
                return;
            }
            $resourceRule->addAction($action);
            $this->userRules->update($resourceRule);
        } else {
            $isString = is_string($resource);
            $resourceRule = $this->userRules->create(
                $this->user,
                $isString ? 'string' : $resource->getClass(),
                $isString ? $resource : $resource->getId(),
                [$action]
            );
            $this->userRules->save($resourceRule);
        }
    }
}