<?php

namespace CirclicalUser\Service;

use CirclicalUser\Entity\Role;
use CirclicalUser\Exception\InvalidRoleException;
use CirclicalUser\Exception\PermissionExpectedException;
use CirclicalUser\Mapper\UserMapper;
use CirclicalUser\Provider\GroupPermissionProviderInterface;
use CirclicalUser\Provider\UserInterface;
use CirclicalUser\Provider\UserPermissionInterface;
use CirclicalUser\Provider\UserInterface as User;
use CirclicalUser\Exception\GuardConfigurationException;
use CirclicalUser\Exception\UnknownResourceTypeException;
use CirclicalUser\Exception\UserRequiredException;
use CirclicalUser\Provider\ResourceInterface;
use CirclicalUser\Provider\UserPermissionProviderInterface;
use CirclicalUser\Provider\RoleInterface;
use CirclicalUser\Provider\RoleProviderInterface;
use phpDocumentor\Reflection\Types\Resource;


class AccessService
{
    const ACCESS_DENIED = 'ACL_ACCESS_DENIED';

    /**
     * @var  UserInterface
     */
    private $user;

    private $userMapper;

    private $controllerDefaults;

    private $actions;

    private $userRoles;

    private $roleProvider;

    private $groupPermissions;

    private $userPermissions;


    public function __construct(array $guardConfiguration, RoleProviderInterface $roleProvider,
                                GroupPermissionProviderInterface $groupPermissionProvider, UserPermissionProviderInterface $userPermissionProvider,
                                UserMapper $userMapper)
    {
        $this->userMapper = $userMapper;
        $this->roleProvider = $roleProvider;
        $this->groupPermissions = $groupPermissionProvider;
        $this->userPermissions = $userPermissionProvider;
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
     * Add a role for the current User
     *
     * @param $roleName
     *
     * @throws InvalidRoleException
     * @throws UserRequiredException
     * @internal param $roleId
     */
    public function addRoleByName($roleName)
    {
        if (!$this->user) {
            throw new UserRequiredException();
        }

        $this->compileUserRoles();

        if ($this->hasRoleWithName($roleName)) {
            return;
        }

        $role = $this->roleProvider->getRoleWithName($roleName);

        if (!$role) {
            throw new InvalidRoleException($roleName);
        }

        $this->user->addRole($role);
        $this->userRoles[] = $roleName;
        $this->userMapper->update($this->user);
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
        $userRoles = $this->user->getRoles();
        if ($userRoles) {
            foreach ($userRoles as $userRole) {
                $roleExpansion[] = $userRole->getName();

                $parentRole = $userRole->getParent();
                while ($parentRole) {
                    $roleExpansion[] = $parentRole->getName();
                    $parentRole = $parentRole->getParent();
                }
            }
        }
        $this->userRoles = array_unique($roleExpansion);
    }

    /**
     * Permissions are an ability to do 'something' with either a 'string' or ResourceInterface as the subject.  Some
     * permissions are attributed to roles, as defined by your role provider.  This method checks to see if the set of
     * roles associated to your user, grants access to a specific verb-actions on a resource.
     *
     * @param ResourceInterface|string $resource
     *
     * @return array
     * @throws UnknownResourceTypeException
     */
    public function getGroupPermissions($resource) : array
    {
        if ($resource instanceof ResourceInterface) {
            return $this->groupPermissions->getResourcePermissions($resource);
        }

        if (is_string($resource)) {
            return $this->groupPermissions->getPermissions($resource);
        }

        throw new UnknownResourceTypeException(get_class($resource));
    }

    /**
     * Permissions can also be defined at a user level.  Similar to group rules (e.g., all admins can 'shutdown' 'servers'),
     * you can give users individual privileges on verbs and resources. You can create circumstances such as
     * "all admins can 'shutdown' 'servers', and user 45 can do it too!"
     *
     * This method expects that a user has been set, e.g., by the Factory.
     *
     * A single permission is returned, since the user can only have one permission set attributed to a given Resource
     *
     * @param ResourceInterface|string $resource
     *
     * @return UserPermissionInterface
     * @throws UnknownResourceTypeException
     * @throws UserRequiredException
     */
    public function getUserPermission($resource)
    {
        if (!$this->user) {
            throw new UserRequiredException();
        }

        if ($resource instanceof ResourceInterface) {
            return $this->userPermissions->getResourceUserPermission($resource, $this->user);
        }

        if (is_string($resource)) {
            return $this->userPermissions->getUserPermission($resource, $this->user);
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
     * @param ResourceInterface|string $resource
     * @param string                   $action
     *
     * @return bool
     */
    public function isAllowed($resource, $action) : bool
    {
        $actions = $this->getGroupPermissions($resource);

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
     * @param ResourceInterface|string $resource
     * @param string                   $action
     *
     * @return bool
     */
    public function isAllowedUser($resource, $action) : bool
    {
        $actionRule = $this->getUserPermission($resource);

        if ($actionRule) {
            if (in_array($action, $actionRule->getActions())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Grant a user, string (simple) or ResourceInterface permissions.  The action whose permission is being granted,
     * must be specified.
     *
     * Example:  $this->grantAccess('car','start');
     *
     * The user must have been loaded in using setUser (done automatically by the factory when a user is authenticated)
     * prior to this call.
     *
     * @param ResourceInterface|string $resource
     * @param string                   $action
     *
     * @throws PermissionExpectedException
     */
    public function grantUserAccess($resource, $action)
    {
        $resourceRule = $this->getUserPermission($resource);

        // make sure we can work with this
        if ($resourceRule) {
            if (!($resourceRule instanceof UserPermissionInterface)) {
                throw new PermissionExpectedException(UserPermissionInterface::class, get_class($resourceRule));
            }
        }

        /** @var UserPermissionInterface $resourceRule */
        if ($resourceRule) {
            if (in_array($action, $resourceRule->getActions())) {
                return;
            }
            $resourceRule->addAction($action);
            $this->userPermissions->update($resourceRule);
        } else {
            $isString = is_string($resource);
            $resourceRule = $this->userPermissions->create(
                $this->user,
                $isString ? 'string' : $resource->getClass(),
                $isString ? $resource : $resource->getId(),
                [$action]
            );
            $this->userPermissions->save($resourceRule);
        }
    }

    /**
     * Revoke access to a resource
     *
     * @param ResourceInterface|string $resource
     * @param                          $action
     *
     * @throws PermissionExpectedException
     */
    public function revokeUserAccess($resource, $action)
    {
        $resourceRule = $this->getUserPermission($resource);

        if (!$resourceRule) {
            return;
        }

        // make sure we can work with this
        if ($resourceRule) {
            if (!($resourceRule instanceof UserPermissionInterface)) {
                throw new PermissionExpectedException(UserPermissionInterface::class, get_class($resourceRule));
            }
        }

        if ($resourceRule) {
            if (!in_array($action, $resourceRule->getActions())) {
                return;
            }
            $resourceRule->removeAction($action);
            $this->userPermissions->update($resourceRule);
        }
    }

}